# Policy Reporter Evaluation Exercise

## Setup Instructions

* The project contains a .ddev directory and is runnable with `ddev start`.
* Once ddev is started, you will need to run `ddev runonce`. This will run several initialization tasks including:
    * running `composer install`
    * handling npm-related setup and building
    * setting up the `.env` file and a secure application key
* At any time you can start the environment with `ddev start`

# Review Instructions

* After starting the project via DDEV, it will be available at https://policyreporter.ddev.site with a very simple frontend that can be used to test the output
* Laravel contains a lot of boilerplate and base files; files which were personally created by me and which are relevant to the assignment are:
    * `/app/Services/StateMachineService.php`
    * `/app/Http/Controllers/StateMachineController.php`
* Tests are of course in the `/tests` directory, both in `/tests/Unit` and `/tests/Feature`
* The view which handles the extremely simple front-end is at `/app/resources/views/welcome.blade.php`

## Additional development notes

* I chose Laravel rather than straight PHP largely because my understanding is that the actual contract job involves working on Laravel projects extensively (and secondarily because I find the built-in testing harness convenient).
* Where I used exceptions, I mostly threw them manually. This is because while Laravel's exception handling and reporting is a very valuable tool, creating custom exceptions adds a lot of weight to a simple project. Throwing exceptions manually allows for custom messages in a much lighter-weight way.
* The StateMachine *Controller* has only Feature tests, not Unit tests. This is for two reasons: first, it is tangential to the actual evaluation (it exists entirely to provide a demonstration); and second, because this is my usual practice for HTTP tests. I do understand that practices may vary.