<?php

namespace Drupal\Core;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpCache\HttpCache as SymfonyHttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

class HttpCache extends SymfonyHttpCache {

    /**
     * Constructor.
     *
     * @param HttpKernelInterface $kernel
     *   An HttpKernelInterface instance
     * @param string $cacheDir
     *   The cache directory (default used if null)
     */
    public function __construct(HttpKernelInterface $kernel, StoreInterface $store, array $options = array()) {
      parent::__construct($kernel, $store, $this->createEsi(), array_merge(array('debug' => $kernel->isDebug()), $this->getOptions()));
    }

    public function cacheEnabled() {
      // @todo: this is a total hack to make tests work. Refactor this out when
      // our chimera of old-skool bootstrap and Symfony is less monstrous.
      drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);
      // @todo: we should inject this config object.
      return config('system.performance')->get('cache.page.enabled');
    }
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
      // Probably a better way to do this.
      if (!$this->cacheEnabled()) {
        $request->headers->set('expect', TRUE);
      }
      return parent::handle($request, $type, $catch);
    }

    /**
     * Forwards the Request to the backend and returns the Response.
     *
     * @param Request $request
     *   A Request instance
     * @param Boolean $raw
     *   Whether to catch exceptions or not
     * @param Response $entry
     *   A Response instance (the stale entry if present, null otherwise)
     *
     * @return Response A Response instance
     */
    protected function forward(Request $request, $raw = false, Response $entry = null) {
      $this->getKernel()->boot();
      $this->getKernel()->getContainer()->set('cache', $this);
      $this->getKernel()->getContainer()->set('esi', $this->getEsi());

      return parent::forward($request, $raw, $entry);
    }


    protected function createEsi() {
      return new Esi();
    }

    protected function getOptions() {
      return array(
        'debug' => TRUE
      );
  }
}
