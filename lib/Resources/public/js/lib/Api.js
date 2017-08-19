ag.ns("ag.api");

(function(){

var successCallback = function(data, textStatus, jqXHR)
{
    var self = this;

    self.ind.halt(function() {
        if (data && data.payload !== undefined && data.entityList !== undefined) {
            data = ag.api.extract(self.responseObjectName, data.payload, data.entityList);
        }
        else if (data instanceof Object) {
            data = ag.api.extract(self.responseObjectName, data, []);
        }

        self.callback(data, jqXHR.status, jqXHR);
    });
},

errorCallback = function(jqXHR)
{
    var self = this;

    if (jqXHR.status === 401 && ag.cfg.reloadOn401)
    {
        self.msgH.alert(jqXHR.responseText, "error", "agit.api", location.reload.bind(location));
    }
    else
    {
        this.ind.halt(function() {
            self.msgH.clear("agit.api");
            self.msgH.alert(jqXHR.responseText || "API request failed.", "error", "agit.api");
            self.callback(null, jqXHR.status, jqXHR);
        });
    }
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
