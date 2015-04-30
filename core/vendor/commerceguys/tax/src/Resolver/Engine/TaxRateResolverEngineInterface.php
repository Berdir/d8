<?php

namespace CommerceGuys\Tax\Resolver\Engine;

use CommerceGuys\Tax\TaxableInterface;
use CommerceGuys\Tax\Model\TaxRateInterface;
use CommerceGuys\Tax\Model\TaxTypeInterface;
use CommerceGuys\Tax\Resolver\Context;
use CommerceGuys\Tax\Resolver\TaxRate\TaxRateResolverInterface;

/**
 * Tax rate resolver engine interface.
 *
 * Sorts the provided tax rate resolvers by priority and invokes them
 * individually until one of them returns a result.
 */
interface TaxRateResolverEngineInterface
{
    /**
     * Adds a resolver.
     *
     * @param TaxRateResolverInterface $resolver The resolver.
     * @param int                      $priority The priority of the resolver.
     */
    public function add(TaxRateResolverInterface $resolver, $priority = 0);

    /**
     * Gets all added resolvers, sorted by priority.
     *
     * @return TaxRateResolverInterface[] An array of tax rate resolvers.
     */
    public function getAll();

    /**
     * Resolves the tax rate by invoking the individual resolvers.
     *
     * @param TaxTypeInterface $taxTypes A previously resolved tax type.
     * @param TaxableInteface  The taxable object.
     * @param Context          $context  The context.
     *
     * @return TaxRateInterface|null The resolved tax rate, or null.
     */
    public function resolve(TaxTypeInterface $taxType, TaxableInterface $taxable, Context $context);
}
