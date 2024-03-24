# NestJS Logging
NestJS comes with a built-in logging system that allows you to log messages at different levels of severity. 
However, the default logger provided by NestJS is a simple console logger that logs messages to the console.

Helios has been customized to override the default logger and use a more advanced logger that can log messages to multiple outputs.  
The logger used by Helios is from the Winston library, and has been configured to log to the console and to Seq.

# Seq
Seq is a centralized logging server that allows you to collect, store, and analyze log messages from multiple sources.
It provides a web interface where you can view and search log messages, create alerts, and more.
To get started with Seq you can download and install it from the [official website](https://datalust.co/seq), or use the docker-compose file in the root of the project.  
After installing, navigate to `http://localhost:5341` to access the Seq web interface.
