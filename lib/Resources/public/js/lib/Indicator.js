ag.ns("ag.api");

ag.api.Indicator = function()
{
    this.start = function() {};

    this.finish = function(callback)
    {
        callback && callback();
    };
};