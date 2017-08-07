ag.ns("ag.api");

ag.api.ApiError = function(message)
{
    this.name = "ApiError";
    this.message = message;
};

ag.api.ApiError.prototype = Object.create(Error.prototype);
