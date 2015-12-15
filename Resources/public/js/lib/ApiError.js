(function(){
    var
        err = function(message)
        {
            this.name = "ApiError";
            this.message = message;
        };

    err.prototype = Object.create(Error.prototype);
    err.prototype.constructor = err;

    Agit.ApiError = err;
})();
