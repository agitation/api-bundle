Agit.Endpoint = function(endpointName)
{
    var
        endpointMeta = Agit.Endpoint.list[endpointName],
        expectedRequest = endpointMeta[0],
        expectedResponse = endpointMeta[1];

    this.getName = function()
    {
        return endpointName;
    };

    this.toString = function()
    {
        return endpointName;
    };

    this.getRequest = function()
    {
        return endpointMeta[0];
    };

    this.getResponse = function()
    {
        return endpointMeta[1];
    };
};

Agit.Endpoint.list = {};

Agit.Endpoint.register = function(endpoints)
{
    Object.keys(endpoints).map(function(key){
        Agit.Endpoint.list[key] = endpoints[key];
    });
};
