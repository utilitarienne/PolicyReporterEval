# Policy Reporter Evaluation Exercise

## Setup & Review Instructions

* The project contains a .ddev directory and is runnable with `ddev start`.
* Once ddev is started, you will need to run `ddev runonce`.
* The project will then be available at https://policyreporter.ddev.site with a very simple frontend that can be used to test the output

## Additional development notes

* Where I used exceptions, I mostly threw them manually. This is because while Laravel's exception handling and reporting is a very valuable tool, creating custom exceptions adds a lot of weight to a simple project. Throwing exceptions manually allows for custom messages in a much lighter-weight way.