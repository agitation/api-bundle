ag.ns("ag.api");

ag.api.ApiError = function(message)
{
    Error.apply(this, arguments);
    this.name = "ApiError";
    this.message = message;
};

ag.api.ApiError.prototype = Object.create(Error.prototype);
