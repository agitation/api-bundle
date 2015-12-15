/*jslint eqeq: true, nomen: true, plusplus: true, sloppy: true, white: true, browser: true, devel: false, maxerr: 500 */
/*global Tx, $, jQuery, OpenLayers, JSON */

Agit.Message = function(text, type, category)
{
    type = type || 'info';
    category = category || '';

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
