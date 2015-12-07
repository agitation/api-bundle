Agit.Indicator = function()
{
    this.start = function() {};

    this.finish = function(callback)
    {
        callback && callback();
    };
};
