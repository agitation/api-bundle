/*global Agit, jQuery */

Agit.Api = (function($){
    var
        normalizePayload = function(payload, entityList)
        {
            var
                pattern = /#e#:[0-9]+/,

                expand = function(value)
                {
                    var newValue = value;

                    if (value instanceof Array)
                    {
                        newValue = [];

                        $.each(value, function(k, v){
                            newValue.push(expand(v));
                        });
                    }
                    else if (value instanceof Object)
                    {
                        newValue = {};

                        $.each(value, function(k, v){
                            newValue[k] =  expand(v);
                        });
                    }
                    else if (typeof(value) === "string" && value.match(pattern))
                    {
                        newValue = expand(entityList[value]);
                    }

                    return newValue;
                };

            return expand(payload);
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

                if (endpoint instanceof Agit.Endpoint)
                    endpoint = endpoint.getName();

                var
                    self = this,
                    callbackWrapper = function(response)
                    {
                        self.ind.finish(function() {
                            response = processResponse(response);

                            self.msgH.clear("agit.api");

                            response.messageList.forEach(function(message){
                                self.msgH.showMessage(new Agit.Message(
                                    message.text,
                                    message.type,
                                    "agit.api"
                                ));
                            });

                            response.payload = normalizePayload(response.payload, response.entityList);
                            callback(params.fullResponse ? response : response.payload);
                        });
                    },

                    ajaxOpts = {
                        type         : "POST",
                        url          : Agit.apiBaseUrl + "/" + endpoint,
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
            ind : { value : ind || new Agit.Indicator() },
            msgH : { value : msgH || new Agit.MessageHandlerAlert() }
        });
    };
})(jQuery);
