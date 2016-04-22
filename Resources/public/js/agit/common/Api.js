agit.ns("agit.common");

(function(){
    var
        entityReferencePattern = /#e#:[0-9]+/,

        normalizePayload = function(responseObjectName, payload, entityList)
        {
            var
                expandEntities = function(value, objName)
                {
                    var newValue = value;

                    if (value instanceof Array)
                    {
                        newValue = [];

                        $.each(value, function(k, v){
                            newValue.push(expandEntities(v, objName));
                        });
                    }
                    else if (value instanceof Object)
                    {
                        if (objName)
                        {
                            newValue = new agit.api.Object(objName);

                            $.each(value, function(prop, val){
                                var meta = newValue.getPropMeta(prop);
                                newValue[prop] = expandEntities(val, meta["class"] || null);
                            });
                        }
                        else
                        {
                            newValue = {};

                            $.each(value, function(k, v){
                                newValue[k] = expandEntities(v);
                            });
                        }
                    }
                    else if (typeof(value) === "string" && value.match(entityReferencePattern))
                    {
                        newValue = expandEntities(entityList[value], objName);
                    }

                    return newValue;
                };

            if (responseObjectName.substr(-2) === "[]")
                responseObjectName = responseObjectName.substr(0, responseObjectName.length - 2);

            return expandEntities(payload, responseObjectName);
        },

        successCallback = function(data, textStatus, jqXHR)
        {
            var self = this;

            self.ind.finish(function() {
                if (data && data.payload && data.entityList)
                    data = normalizePayload(self.responseObjectName, data.payload, data.entityList);

                self.callback(data, jqXHR.status, jqXHR);
            });
        },

        errorCallback = function(jqXHR, textStatus)
        {
            var self = this;

            this.ind.finish(function() {
                self.msgH.clear("agit.api");
                self.msgH.alert(jqXHR.responseText || "API request failed.",  "error", "agit.api");
                self.callback(null, jqXHR.status, jqXHR);
            });
        };

    agit.common.Api = function(ind, msgH)
    {
        this.ind = ind || new agit.api.Indicator();
        this.msgH = msgH || new agit.common.MessageHandler();
    };

    agit.common.Api.prototype.doCall = function(endpoint, request, callback)
    {
        if (typeof(endpoint) === "string")
            endpoint = new agit.api.Endpoint(endpoint);

        var
            callbackParams = {
                ind: this.ind,
                msgH : this.msgH,
                callback : callback,
                responseObjectName : endpoint.getResponse()
            },

            ajaxOpts = {
                type         : "POST",
                url          : agit.cfg.apiBaseUrl + "/" + endpoint.getName(),
                data         : "request=" + JSON.stringify(request).replace(/\+/g, "%2b").replace(/&/g, "%26"),
                success      : successCallback.bind(callbackParams),
                error        : errorCallback.bind(callbackParams),
                headers      : {},
                dataType     : "json"
            };

        if (agit.cfg.csrfToken)
            ajaxOpts.headers["x-token"] = agit.cfg.csrfToken;

        ajaxOpts.headers["x-api-serialize-compact"] = "true";

        this.ind.start();

        $.ajax(ajaxOpts);
    };
})();
