ag.ns("ag.api");

(function() {

var indicator = function() { };

indicator.prototype = Object.create(jQuery.prototype);

indicator.prototype.start = function() {};

indicator.prototype.halt = function(callback)
{
    callback && callback();
};

ag.api.Indicator = indicator;

}());
