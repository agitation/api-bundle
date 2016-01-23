agit.ns("agit.api");

agit.api.Indicator = function()
{
    this.start = function() {};

    this.finish = function(callback)
    {
        callback && callback();
    };
};
