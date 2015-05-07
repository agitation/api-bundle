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

    call namespace      ^^^^^^^^^
    namespace version             ^^
    endpoint name                    ^^^^^^^^^^^^^^^
    call name                                        ^^^^^^^^^^^
    actual request via GET/POST                                  ^^^^^^^^^^^^^^^^^^^^^
```


## API annotations

The endpoints, calls and objects are annotated, these annotations provide
meta information to ensure consistent API calls, such as:

- Authentication/authorization: Endpoint calls define the required capabilities (through `agitation/user`) to access a call.
- Validation: Expected request/response objects, and their allowed values.
- Automatical documentation: The AgitSdkDocBundle provides tools to automatically generate Markdown documentation.
- Export API endpoints and objects to JavaScript for simple client-side usage.

## Examples

### Endpoints

An example endpoint looks like this:

```
<?php

namespace Acme\DemoBundle\Api\v1\Endpoint;

use Agit\ApiBundle\Api\Meta;
use Agit\ApiBundle\Api\Endpoint\AbstractEndpoint;
use Agit\ApiBundle\Api\Object\AbstractObject;

/**
 * The class name is also the endpoint name.
 */
class ExampleEndpoint extends AbstractEndpoint
{
    /**
     * @Meta\Call\Call(request="MyRequestObject",response="othernamespace.v1/SomeResponseObject")
     * @Meta\Call\Security(capability="administrator",allowCrossOrigin=false)
     *
     * This is the `doSomething` call of the `ExampleEndpoint` endpoint.
     */
    protected function doSomething(AbstractObject $RequestObject)
    {
        // get the value of $foo and process it
        $foo = $RequestObject->get('foo');

        // ...

        // now generate the response
        $Response = $this->createObject('othernamespace.v1/SomeResponseObject');
        $Response->set("some", "value");
        return $Response;
    }
}
```

### Objects

An example object looks like this:

```
<?php

namespace Acme\DemoBundle\Api\v1\Object;

use Agit\ApiBundle\Api\Object\AbstractObject;
use Agit\ApiBundle\Api\Meta\Property;

/**
 * The class name is the API object name.
 */
class MyRequestObject extends AbstractObject
{
    /**
     * @Property\StringType(minLength=3,maxLength=40)
     *
     * The above annotation ensures that the `foo` property is present in the
     * request object, that it's a string, and that this string is between
     * 3 and 40 characters long.
     */
    public $foo;
}
```
