Compodoc is a documentation tool specifically designed for Angular / NestJS applications. It **generates** a static website based on your application's **source code and JSDoc comments**. 
The generated website includes details about your modules, services, routes, classes, interfaces, decorators, and more, providing a complete overview of your application's structure and functionality.

Here's how to use Compodoc and update the documentation for your project:

1. **Understanding the Commands**

    - `npm run docs:gen`: This command generates the documentation based on your application's source code and JSDoc comments. The generated documentation is placed in the `./documentation/generated` directory.

    - `npm run docs:serve`: This command not only generates the documentation but also starts a local server and opens the generated documentation website in your default web browser.

2. **Adding Additional Markdown Documentation**

   You can add additional Markdown documentation to your project by creating new `.md` files in the `./documentation/includes` directory. Each `.md` file you add corresponds to a separate page of the documentation.
   When adding custom `.md` files, they also need to be added to the `summary.json` inside the `./documentation/includes` directory. This file is required so compodoc knows at which level to put this page, as you can also nest pages.
    [More info here](https://compodoc.app/guides/tips-and-tricks.html#additional-documentation)

3. ** Configuring Compodoc**
    To adjust the configuration of Compodoc, you can modify the `.compodocrc.json` file in the root of the project. Please refer to the official documentation for configuration options.


Remember to commit and push your changes to the repository, so that others can access the updated documentation.
