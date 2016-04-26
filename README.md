**Agitation** is an e-commerce framework, based on Symfony2, focussed on
extendability through plugged-in APIs, UIs, payment modules and other
components.

## AgitApiBundle

This bundle provides a pluggable API handler. It allows other bundles to define
their own, independent API endpoints, calls and request/response objects.

## API URLs

A simple call may look like this:

```
https://example.com/api/namespace.v1/ExampleEndpoint.doSomething?request={"foo":"bar"}

    namespace           ^^^^^^^^^
    namespace version             ^^
    controller name                  ^^^^^^^^^^^^^^^
    endpoint name                                    ^^^^^^^^^^^
    actual request                                               ^^^^^^^^^^^^^^^^^^^^^
```

## API annotations

The controllers, endpoints and objects are annotated, these annotations provide meta information to ensure consistent API calls, such as:

- Authentication/authorization: Endpoint calls define the required capabilities (through `agitation/user`) to access a call.
- Validation: Expected request/response objects, and their allowed values.
- Automatical documentation: The AgitSdkDocBundle provides tools to automatically generate Markdown documentation.
- Export API endpoints and objects to JavaScript for simple client-side usage.
