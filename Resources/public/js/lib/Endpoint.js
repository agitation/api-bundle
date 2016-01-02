Agit.Endpoint = function(endpointName)
{
    var
        endpointMeta = Agit.Endpoint.list[endpointName];

    if (!endpointMeta)
        throw new Agit.ApiError("No meta was loaded for " + endpointName);

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
