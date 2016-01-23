agit.ns("agit.api");

agit.api.Endpoint = function(endpointName)
{
    var
        endpointMeta = agit.api.Endpoint.list[endpointName];

    if (!endpointMeta)
        throw new agit.api.ApiError("No meta was loaded for " + endpointName);

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

agit.api.Endpoint.list = {};

agit.api.Endpoint.register = function(endpoints)
{
    Object.keys(endpoints).map(function(key){
        agit.api.Endpoint.list[key] = endpoints[key];
    });
};
