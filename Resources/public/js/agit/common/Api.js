agit.ns("agit.common");


agit.common.Api = (function($){
    var
        normalizePayload = function(responseObjectName, payload, entityList)
        {
            responseObjectName.substr(-2) === "[]" && (responseObjectName = responseObjectName.substr(0, responseObjectName.length - 2));

            var
                pattern = /#e#:[0-9]+/,

                expand = function(value, objName)
                {
                    var newValue = value;

                    if (value instanceof Array)
                    {
                        newValue = [];

                        $.each(value, function(k, v){
                            newValue.push(expand(v, objName));
                        });
                    }
                    else if (value instanceof Object)
                    {
                        if (objName)
                        {
                            newValue = new agit.api.Object(objName);

                            $.each(value, function(prop, val){
                                var meta = newValue.getPropMeta(prop);
                                newValue[prop] = expand(val, meta["class"] || null);
                            });
                        }
                        else
                        {
                            newValue = {};

                            $.each(value, function(k, v){
                                newValue[k] = expand(v);
                            });
                        }
                    }
                    else if (typeof(value) === "string" && value.match(pattern))
                    {
                        newValue = expand(entityList[value], objName);
                    }

                    return newValue;
                };

            return expand(payload, responseObjectName);
        },

        // this is to make sure that, in the event of an error, we have a "proper" response.
        processResponse = function(response)
        {
            if (!response ||
                typeof(response) !== "object" ||
                response.payload === undefined ||
                response.messageList === undefined)
            {
                response =
                {
                    success : false,
                    payload : null,
                    messageList : [{ type: "error", text: "Error while loading the requested data." }]
                };
            }

            return response;
        },

        apiProto =
        {
            doCall : function(endpoint, request, callback, params)
            {
                params = params || {};

                typeof(endpoint) === "string" && (endpoint = new agit.api.Endpoint(endpoint));

                var
                    self = this,
                    callbackWrapper = function(response)
                    {
                        self.ind.finish(function() {
                            response = processResponse(response);

                            self.msgH.clear("agit.api");

                            response.messageList.forEach(function(message){
                                self.msgH.showMessage(new agit.common.Message(
                                    message.text,
                                    message.type,
                                    "agit.api"
                                ));
                            });

                            response.payload = normalizePayload(endpoint.getResponse(), response.payload, response.entityList);
                            callback(params.fullResponse ? response : response.payload);
                        });
                    },

                    ajaxOpts = {
                        type         : "POST",
                        url          : Agit.apiBaseUrl + "/" + endpoint.getName(),
                        data         : "request=" + JSON.stringify(request).replace(/\+/g, "%2b").replace(/&/g, "%26"),
                        success      : callbackWrapper,
                        error        : callbackWrapper,
                        dataType     : params.dataType || "json"
                    };

                if (ajaxOpts.dataType === "jsonp")
                {
                    ajaxOpts.type = "GET";
                    ajaxOpts.url += ".jsonp";
                }

                if (Agit.csrfToken)
                {
                    ajaxOpts.headers = { "X-Token" : Agit.csrfToken };
                }

                this.ind.start();

                $.ajax(ajaxOpts);
            }
        };

    return function(ind, msgH)
    {
        return Object.create(apiProto, {
            ind : { value : ind || new agit.widget.Indicator() },
            msgH : { value : msgH || new agit.msgh.protoAlert() }
        });
    };
})(jQuery);
