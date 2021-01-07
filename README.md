# MiniLoader

 Miniloader installs and/or loads the current irfantoor/test package and its dependencies, specially for the classes required by irfantoor/test.

 It helps circumventing the cyclic redendency, while loading the irfantoor/test package.

 MiniLoader tries to locate a package, which can not be included by composer because of dependency constraints, installs it and then locates the required classes (in package_dir/src/) and includes these if found.

 The package is not intended for a general purpose use, so if you want to use it with your own packages, kindly use it with caution. All your suggestions are welcome.
