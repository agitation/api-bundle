ag.ns("ag.api");

ag.api.Endpoint = function(endpointName)
{
    var
        endpointMeta = ag.api.Endpoint.list[endpointName];

    if (!endpointMeta)
        throw new ag.api.ApiError("No meta was loaded for " + endpointName);

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

ag.api.Endpoint.list = {};

ag.api.Endpoint.register = function(endpoints)
{
    Object.keys(endpoints).map(function(key){
        ag.api.Endpoint.list[key] = endpoints[key];
    });
};
