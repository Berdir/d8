<?php

/**
 * @file
 * Contains \Drupal\Core\Render\MainContent\SmartCacheHtmlRenderer.
 */

namespace Drupal\Core\Render\MainContent;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheContextsManager;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Render\RenderCacheInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * SmartCache main content renderer for HTML requests.
 */
class SmartCacheHtmlRenderer extends HtmlRenderer {

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\CacheContextsManager
   */
  protected $cacheContextsManager;

  /*
   * A policy rule determining the cacheability of a request.
   *
   * @var \Drupal\Core\PageCache\RequestPolicyInterface
   */
  protected $requestPolicy;

  /**
   * A policy rule determining the cacheability of the response.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface
   */
  protected $responsePolicy;

  /**
   * The Smart Cache contexts cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $smartContextsCache;

  /**
   * The Smart Cache #type => html render array cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $smartHtmlCache;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new SmartCacheHtmlRenderer.
   *
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $display_variant_manager
   *   The display variant manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Render\RenderCacheInterface $render_cache
   *   The render cache service.
   * @param \Drupal\Core\Cache\CacheContextsManager $cache_contexts_manager
   *   The cache contexts service.
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $request_policy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $response_policy
   *   A policy rule determining the cacheability of the response.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Cache\CacheBackendInterface $contexts_cache
   *   The Smart Cache contexts cache bin.
   * @param \Drupal\Core\Cache\CacheBackendInterface $html_cache
   *   The Smart Cache #type => html render array cache bin.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(TitleResolverInterface $title_resolver, PluginManagerInterface $display_variant_manager, EventDispatcherInterface $event_dispatcher, ModuleHandlerInterface $module_handler, RendererInterface $renderer, RenderCacheInterface $render_cache, CacheContextsManager $cache_contexts_manager, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, RouteMatchInterface $route_match, CacheBackendInterface $contexts_cache, CacheBackendInterface $html_cache, RequestStack $request_stack) {
    parent::__construct($title_resolver, $display_variant_manager, $event_dispatcher, $module_handler, $renderer, $render_cache);
    $this->cacheContextsManager = $cache_contexts_manager;
    $this->requestPolicy = $request_policy;
    $this->responsePolicy = $response_policy;
    $this->routeMatch = $route_match;
    $this->smartContextsCache = $contexts_cache;
    $this->smartHtmlCache = $html_cache;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  protected function finish(array $html) {
    // If this is a #type => html render array that comes from SmartCache
    // already, then we can return early: no need to redo all the work.
    if (isset($html['#smartcache'])) {
      // Mark the response as a cache hit.
      $html['#attached']['http_header'][] = ['X-Drupal-SmartCache',  'HIT'];
      return parent::finish($html);
    }

    // Don't cache the render array if the associated response will not meet the
    // SmartCache request & response policies.
    $response = new Response();
    $request = $this->requestStack->getCurrentRequest();
    if ($this->requestPolicy->check($request) === RequestPolicyInterface::DENY || $this->responsePolicy->check($response, $request) === ResponsePolicyInterface::DENY) {
      return parent::finish($html);
    }

    $cacheable_html = $html;

    // Get the contexts by which the current route's response must be varied.
    $contexts_cid = 'smartcache:contexts:' . $this->cacheContextsManager->convertTokensToKeys(['route'])[0];
    $stored_cache_contexts = $this->smartContextsCache->get($contexts_cid);
    if ($stored_cache_contexts !== FALSE) {
      $stored_cache_contexts = $stored_cache_contexts->data;
    }

    // "Soft-render" the HTML regions (don't execute #post_render_cache yet,
    // since we must cache the placeholders, not the replaced placeholders).
    foreach (Element::children($cacheable_html) as $child) {
      $this->renderer->render($cacheable_html[$child]);
    }

    // Iterate over all the html template regions (page, page_top, page_bottom)
    // and replace them with the equivalent cacheable render array. At the same
    // time, collect the total set of cache contexts (to update the stored cache
    // contexts, if any), and the total set of cache tags (to associate with the
    // smart_cache_html cache item).
    $html_cache_max_age = Cache::PERMANENT;
    // SmartCache always caches per route, so always include that cache context.
    $html_cache_contexts = ['route'];
    $html_cache_tags = ['rendered'];
    foreach (Element::children($cacheable_html) as $child) {
      $cacheable_html[$child] = $this->renderCache->getCacheableRenderArray($cacheable_html[$child]);
      $html_cache_contexts = Cache::mergeContexts($html_cache_contexts, $cacheable_html[$child]['#cache']['contexts']);
      $html_cache_tags = Cache::mergeTags($html_cache_tags, $cacheable_html[$child]['#cache']['tags']);
      $html_cache_max_age = Cache::mergeMaxAges($html_cache_max_age, $cacheable_html[$child]['#cache']['max-age']);
    }

    // Retain page titles defined in the main content render array.
    if (isset($html['page']['#title'])) {
      $cacheable_html['page']['#title'] = $html['page']['#title'];
    }

    // @todo DEBUG DEBUG DEBUG PROFILING PROFILING PROFILING — Until only the
    //   truly uncacheable things set max-age = 0 (such as the search block and
    //   the breadcrumbs block, which currently set max-age = 0, even though it
    //   is perfectly possible to cache them), to see the performance boost this
    //   will bring, uncomment this line.
//    $html_cache_max_age = Cache::PERMANENT;

    // @todo Remove this. Work-around to support the deep-render-array-scanning-
    //    dependent logic bartik_preprocess_html() has: it needs to know about
    //    the presence or absence of certain regions. That is similar (but less
    ///   bad) to the evil things one could do with hook_page_alter() in D7.
    foreach (Element::children($html['page']) as $page_region) {
      $cacheable_html['page'][$page_region] = ['#preprocess_functions_messing_with_cacheability' => TRUE];
    }

    // SmartCache only caches cacheable pages.
    if ($html_cache_max_age !== 0) {
      $html_cache_contexts = $this->cacheContextsManager->optimizeTokens($html_cache_contexts);
      // If the set of cache contexts is different, store the union of the already
      // stored cache contexts and the contexts for this request.
      if ($html_cache_contexts !== $stored_cache_contexts) {
        if (is_array($stored_cache_contexts)) {
          $html_cache_contexts = $this->cacheContextsManager->optimizeTokens(Cache::mergeContexts($html_cache_contexts, $stored_cache_contexts));
        }
        $this->smartContextsCache->set($contexts_cid, $html_cache_contexts);
      }

      // Finally, cache the #type => html render array by those contexts.
      $cid = 'smartcache:html_render_array:' . implode(':', $this->cacheContextsManager->convertTokensToKeys($html_cache_contexts));
      $expire = ($html_cache_max_age === Cache::PERMANENT) ? Cache::PERMANENT : (int) $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME') + $html_cache_max_age;
      $this->smartHtmlCache->set($cid, $cacheable_html, $expire, $html_cache_tags);

      // Now that the cacheable HTML is cached, mark the response as a cache miss.
      $cacheable_html['#attached']['http_header'][] = ['X-Drupal-SmartCache',  'MISS'];
    }
    else {
      // Now that the cacheable HTML is cached, mark the response as a cache miss.
      $cacheable_html['#attached']['http_header'][] = ['X-Drupal-SmartCache',  'UNCACHEABLE'];
    }

    return parent::finish($cacheable_html);
  }

}
