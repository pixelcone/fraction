<?php

namespace Pixelcone\Fraction;

/**
 * This trait provides a shorthand for instantiating any
 * class and immediately calling some of its methods.
 *
 * Usage example:
 * 1. Import this trait into any class
 * 2. Implement handle() method in this class; this should
 *      be the only public method in it
 * 3. In place where you need to create an object of this
 *      class, call to its static run() method, passing the
 *      same arguments as defined in class' constructor
 * 4. Voilà! You have just created an object and ran its
 *      handle() method using a single call!
 *
 * As this trait requires host class to have a public method
 * for calling, the best usage scenario for it is so-called
 * action classes: plain, standalone PHP classes that contain
 * the logic for one specific task (actually, this trait is
 * originally created as a supplement for such classes).
 *
 * More about action classes in the project's README.md.
 */
trait AsAction
{
    /**
     * Instantiates a class and immediately calls any method
     * from it (by default, a handle() method).
     *
     * @param  mixed  $data - a set of arguments, passed to
     *                    class' constructor; this can be
     *                    either a set of comma-separated
     *                    parameters or an array with keys
     *                    equal to constructor's params
     *                    and values as these param's values
     */
    public static function run(mixed $data = []): mixed
    {
        if (func_num_args() > 1) {
            $data = func_get_args();
        }

        return (new ObjectBuilder(static::class))->run($data);
    }
}
