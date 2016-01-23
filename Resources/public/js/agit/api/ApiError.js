agit.ns("agit.api");

(function(){
    var
        err = function(message)
        {
            this.name = "ApiError";
            this.message = message;
        };

    err.prototype = Object.create(Error.prototype);
    err.prototype.constructor = err;

    agit.api.ApiError = err;
})();
