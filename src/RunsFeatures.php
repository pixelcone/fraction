<?php

namespace Pixelcone\Fraction;

/**
 * This trait is a helper unit that offers a shorthand for
 * calling Feature classes (more about Feature classes in
 * the project's README.md).
 *
 * This trait is almost the same as the AsAction one,
 * with one small difference: it is intended for classes
 * WHICH CALL other classes, not for the ones WHICH ARE
 * CALLED.
 *
 * Usage example:
 * 1. Import this trait into any controller/command class
 * 2. Create a plain Feature class and implement handle()
 *      method in it; this should be the only public method
 *      in this class
 * 3. Call $this->run() method from controller/command,
 *      passing Feature class' FQN as a first argument and
 *      an array of key-value pairs, corresponding to the
 *      Feature class' constructor, as a second one
 * 4. Voilà! You have just instantiated a Feature class
 *      and ran its handle() method using a single call!
 *
 * This trait grew from the concept of an intermediate
 * layer between controllers/commands and action classes.
 * The main idea behind this trait is to make Feature
 * classes be easily accessible from within
 * controllers/commands while keeping former and latter as
 * clean as possible.
 */
trait RunsFeatures
{
    /**
     * Instantiates a Feature class and immediately calls
     * any method from it (by default, a handle() method).
     *
     * @param  string  $class - a Feature class' FQN
     * @param  mixed  $data - a set of arguments, passed to
     *                    teh Feature class' constructor;
     *                    this can be either a set of
     *                    comma-separated parameters or an
     *                    array with keys equal to
     *                    constructor's params and values
     *                    as these param's values
     */
    public function run(string $class, mixed $data = []): mixed
    {
        if (func_num_args() > 2) {
            $data = func_get_args();

            array_shift($data); // don't pass class name as a constructor param
        }

        return (new ObjectBuilder($class))->run($data);
    }
}
