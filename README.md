# Challenge for Frontend Engineer
To better assess a candidates development skills, we would like to provide the following challenge.  You have as much time as you'd like (though we ask that you not spend more than a few hours).

## Submission Instructions
1. First, fork this project on github.  You will need to create an account if you don't already have one.
1. Next, complete the project as described below within your fork.
1. Finally, push all of your changes to your fork on github and submit a pull request.  Email us at info@citizennet.com to review your solution.

## Project Description
A large part of our company is retrieving data from various APIs and representing this data directly or indirectly to the user. One problem with coding for any API is you have to plan for outage. Your task is to write a program to reliably pull Posts and Likes json files from the provided API, and represent this data in a web app for the user.

Here's what your web-based application must do:

1. Write two separate programs that can be run nightly to pull the API information and cache the results to disk. Plan for more programs to be developed that will follow the same pattern, so code it modularity and in sound OOP design. Additionally, code for redundancy, the below API resources are on an commodore 64 with bad wiring, so you might experience 503 errors.
1. Develop an interactive, user friendly web page to display the Post and Like data that was pulled and cached to disk. The direction is left to you, but it has to be creative and interactive. Additional external API usage only adds points

## Pro Tips
1. Keep the changesets as small as possible. Use a dependency manager.
1. Make it easy for anyone to build your project.
1. Consistent use of coding standards.
1. In Colin Powell fashion, we are looking for strength, not lack of weakness. Show off.

## Programming Requirements and Limits:
1. php 5.3+
1. javascript
1. jquery and/or angularjs (angularjs a big plus)
1. html
1. css

## API Resources:
1. http://rack1.citizennet.com/interviewtest/api?file=posts.json&access_token=AAAAAL2uajO8BAPcqOwZB6
1. http://rack1.citizennet.com/interviewtest/api?file=likes.json&access_token=AAAAAL2uajO8BAPcqOwZB6

## Developer notes:

### Environment setup:
1. Download and install the latest version of Vagrant on your system ( http://www.vagrantup.com )
1. Download and install the latest version of VirtualBox on your system ( https://www.virtualbox.org )
1. Git clone this project on your system
1. On a shell or command console:
    - change to the project directory (not the subdirectory named `project`)
    - run `git submodule init` followed by `git submodule update` to git-clone the required puppet modules
    - run `vagrant up`
        * This will download and boot a complete Ubuntu 14.04 virtual machine and install all required packages via puppet, apt-get and composer, and forward http://localhost:8088 to port 80 on the virtual machine. The first time executing this can take 30 to 45 minutes on a reasonably fast connection, but the Ubuntu image is cached, so a complete box rebuild after a `vagrant destroy` should take less than 10 minutes.
    - On the VM, type `cd /vagrant ; phpunit` to run all the project unit tests
        * To close the ssh shell type `exit` or press control+D at the top-most bash prompt.
1. To shutdown the virtual machine to be used again later, type `vagrant halt` otherwise type `vagrant destroy` to shutdown the VM and delete it from your system.

### Project Layout:
+ The virtual machine configuration is contained in `Vagrantfile` and the `puppet` subdirectory.
+ The web application is built on Symfony and Doctrine and lives in the `project` subdirectory.
    - The document root is `project/web` so no unintended files are reachable via the web server.
    - The application configuration lives in `project/app`.
    - All the custom code for this project lives in `project/src/LinuxDr/CitizenNetCnfcBundle`.

### Project Status:
+ As stated earlier, all of the code I've written thus far is server side model code that lives in `project/src/LinuxDr/CitizenNetCnfcBundle/Entity` with unit tests in `project/src/LinuxDr/CitizenNetCnfcBundle/Tests/Entity`.
+ While I am embarrassed at my current progress, I'm happy with the work I did on generic model tests in ...`Tests/Entity/EntityTestBase.php`.
+ I am glad to complete this exercise if you still see value in my efforts.

