/*jslint eqeq: true, nomen: true, plusplus: true, sloppy: true, white: true, browser: true, devel: false, maxerr: 500 */
/*global Tx, $, jQuery, OpenLayers, JSON */

Agit.Message = function(text, _type, _category)
{
    var
        type = _type || 'info',
        category = _category || '';

    this.getType = function()
    {
        return type;
    };

    this.getText = function()
    {
        return text;
    };

    this.getCategory = function()
    {
        return category;
    };
};
