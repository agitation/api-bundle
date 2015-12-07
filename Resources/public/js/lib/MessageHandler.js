/*global Agit */

Agit.MessageHandler =
{
    /**
     * If the handler shows multiple messages at once, this
     * method clears/removes the currently shown messages.
     *
     * If the category parameter is passed, only messages of that
     * category are cleared.
     */
    clear : function(category) { },

    showMessage : function(message)
    {
        alert(message.getText());
    }
};
