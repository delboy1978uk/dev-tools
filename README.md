# dev-tools
Codeception, GrumPHP, PHPStan, and dev utilities, in one easy package
## installation
```
composer require --dev delby1089uk/dev-tools
```
## usage
Use Codeception and PHPStan as normal.
### ReflectionInvoker
Use the trait wherever you need to use reflection
### bin/jirahook
This will take a branch in this format `feature/ABC-1234/whatever` and will auto start your commit message with
```
ABC-1234 : 
```
