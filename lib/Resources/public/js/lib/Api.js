ag.ns("ag.api");

(function(){
    var
        entityReferencePattern = /#e#:[0-9]+/,

        // some API objects have meta fields describing the object itself
        knownMetaFields = {"realClass" : "_class"},

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
                        if (value[knownMetaFields.realClass])
                            objName = value[knownMetaFields.realClass];

                        if (objName)
                        {
                            newValue = new ag.api.Object(objName);

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
                if (data && data.payload !== undefined && data.entityList !== undefined) {
                    data = normalizePayload(self.responseObjectName, data.payload, data.entityList);
                }
                else if (data instanceof Object) {
                    data = normalizePayload(self.responseObjectName, data, []);
                }

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

    ag.api.Api = function(ind, msgH)
    {
         // these will be used as defaults; they can be overridden per call
        this.defaultInd = ind || new ag.api.Indicator();
        this.defaultMsgH = msgH || new ag.common.MessageHandler();
    };

    ag.api.Api.prototype.doCall = function(endpoint, request, callback, indicator, messageHandler)
    {
        if (typeof(endpoint) === "string")
            endpoint = new ag.api.Endpoint(endpoint);

        indicator = indicator || this.defaultInd;
        messageHandler = messageHandler || this.defaultMsgH;

        var
            callbackParams = {
                ind: indicator,
                msgH : messageHandler,
                callback : callback,
                responseObjectName : endpoint.getResponse()
            },

            ajaxOpts = {
                type         : "POST",
                url          : ag.cfg.apiBaseUrl + "/" + endpoint.getName(),
                data         : "request=" + JSON.stringify(request).replace(/\+/g, "%2b").replace(/&/g, "%26"),
                success      : successCallback.bind(callbackParams),
                error        : errorCallback.bind(callbackParams),
                headers      : { "Accept-Language" : ag.cfg.locale },
                dataType     : "json"
            };

        if (ag.cfg.csrfToken)
            ajaxOpts.headers["x-token"] = ag.cfg.csrfToken;

        ajaxOpts.headers["x-api-serialize-compact"] = "true";

        indicator.start();

        $.ajax(ajaxOpts);
    };
})();
