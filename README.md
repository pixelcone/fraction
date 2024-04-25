# Fraction

Fraction is a set of abstractions offering PHP devs a convenient way of storing a business logic in their apps.

The idea for this package was born during the process of refactoring a large project written in Laravel.
This package was inspired by many different works (packages, articles) of other authors. Here are some of them:
- [Laravel Actions (package)](https://www.laravelactions.com/)
- [Lucid Arch (package)](https://lucidarch.dev/)
- [Laravel AAAS (article)](https://wendelladriel.com/blog/laravel-aaas-actions-as-a-service/#how-to-implement-the-aaas-pattern)

## Prerequisites

Before you begin, ensure you have met the following requirements:
* PHP ^8.0

There are really no any other requirements. The package is Laravel-friendly, so if you install it into a Laravel project,
you will be able to use dependency injection within the `handle()` method of your Actions/Features (more of them down below).

## Installing Fraction

To install Fraction, run the `composer require` command:

```bash
composer require pixelcone/fraction:^0.1.1
```

## Using Fraction

This package introduces two abstract layers: Actions and Features.

### Actions

Actions are basic "Units of Life" of the entire application. In a nutshell, Actions are regular PHP classes, focusing on
performing one specific task only (for example, saving a user into DB or sending an email). Actions can accept different
parameters via constructor and can be called from anywhere in the code (preferably from Applications or within the same
Domain only, though). It is highly recommended to have only one public method in each action class to help developers
avoid the temptation of polluting it with extra logic. A name of the Action should reflect what it actually does by
answering the question "what should it do?". Actions are parts of Domains, residing in their own `Actions` folder.
Actions can also be used as Laravel Jobs.

An example of an Action class and its usage:

```php
<?php

namespace App\Actions;

use App\Models\User;
use Pixelcone\Fraction\AsAction;

class CreateUserAction {
    use AsAction;

    public function __construct(
        protected string $fullName;
        protected string $email;
        protected string $password;
    )
    {}
    
    public function handle ()
    {
        // User storing logic here.
        // This method can return anything
        // or can return nothing.
        
        [$firstName, $lastName] = $this->getFullNameParts();
        
        $user = new User();
        
        $user->create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $this->email,
            'password'   => bcrypt($this->password);
        ]);
    }
    
    protected function getFullNameParts (): array
    {
        // this method does some internal job
        // and is accessible within
        // this particular Action only
        
        return explode(' ', $this->fullName);
    }
}
```

Thanks to the `Pixelcone\Fraction\AsAction` trait, this Action class can be now called like this:

```php
CreateUserAction::run('Alex Doe', 'alex@gmail.com', 'pass');
```

We've just instantiated the Action class and run a `handle()` method using a single expression!

### Features

Features have a lot in common with Actions: they're regular PHP classes too, they accept different params through
constructor, and they also can have one custom public `handle` method. They have different purpose, though: they act as
an intermediate layer between controllers/commands and actions. It is a fairly common case when controller or command
methods get bigger over time, making it more difficult to read them. Features are designed to solve this problem, acting
as a controller/command action handler: processing input, calling action classes and forming a response. Features should
not be responsible for running actual business logic, though. Simply put, all the content of a particular controller or
command's method goes inside the Feature. And because it's now there, it can be divided into smaller methods. With that
being said, Features must be called from controllers/commands only. Similar to Action, the name of the Feature should
answer the question "what should it do?".

A typical feature's tasks include (but not limited to):

1. Receiving user input and passing it further along the call chain
2. Calling different Actions
3. Forming and returning a response

An example of a Feature class and its usage:

```php
<?php

namespace App\Http\Features;

use App\Models\User;

class ShowProfilePageFeature {    
    public function handle ($request)
    {
        // Feature logic here.
        // This method may return any kind of
        // response if the outer code requires it.
        
        $user = User::getCurrent();
        $userMessages = $this->getUserMessages($user);
        
        return render('profile', compact('user', 'userMessages'));
    }
    
    protected function profileIsValid (User $user): array
    {
        // Some complex logic here.
        // This method does some internal job
        // and is accessible within
        // this particular Feature only.
        
        return $user->messages();
    }
}
```

With help of the `Pixelcone\Fraction\RunsFeatures` trait, this class can now be conveniently called within the
controller's method:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Features\ShowProfilePageFeature;
use Pixelcone\Fraction\RunsFeatures;

class ProfileController {
    use RunsFeatures;

    // ...
    
    public function index ()
    {
        return $this->run(ShowProfilePageFeature::class);
    }
    
    // ...
}
```

## Contributing to Fraction
To contribute to Fraction, follow these steps:

1. Fork this repository.
2. Create a branch: `git checkout -b <branch_name>`.
3. Make your changes and commit them: `git commit -m '<commit_message>'`
4. Push to the original branch: `git push origin pixelcone/fraction`
5. Create the pull request.

Alternatively, see the GitHub documentation on [creating a pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).

## Contact

If you want to contact me you can reach me at <info@delho.ee>.

## License

This project uses the following license: [MIT](https://spdx.org/licenses/MIT.html).