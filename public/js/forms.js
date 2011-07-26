/**
 * Class Forms to manage forms.
 *
 * @author Alberto Ramírez
 */
Forms =
{
    /**
     * Method to handle forms sent and build ajax request.
     *
     * @param string class_name Form class name.
     */
    handleSent: function(sClassName, oManipulateResponse, fCallbackPreSend)
    {
        var self = this;

        $("form." + sClassName).each(function(){
            $(this).submit(function(eEvent)
            {
                var oForm = eEvent.target;

                if (typeof fCallbackPreSend !== "undefined" && false === fCallbackPreSend(oForm))
                {
                    return false;
                }

                jQuery.ajax(
                {
                    url: $(oForm).attr("action"),
                    async: true,
                    type: "POST",
                    data: $(oForm).serialize(),
                    success: function(oReponse)
                    {
                        return self.onAjaxSuccess(oManipulateResponse, oReponse, oForm);
                    },
                    error: function()
                    {
                        return self.onAjaxError(oManipulateResponse, oForm);
                    },
                    timeout: function()
                    {
                        return self.onAjaxTimeout(oManipulateResponse, oForm);
                    }
                });

                return false;
            })
        });
    },

    /**
     * Method to execute on AJAX request success.
     *
     * @param Object oManipulateResponse Object to manipulate response.
     * @param Object oReponse Data of server.
     */
    onAjaxSuccess: function(oManipulateResponse, oReponse, oForm)
    {
        oManipulateResponse.success(oReponse, oForm);
    },

    /**
     * Method to execute on AJAX request error.
     *
     * @param Object oManipulateResponse Object to manipulate response.
     */
    onAjaxError: function(oManipulateResponse, oForm)
    {
        oManipulateResponse.error(oForm);
    },

    /**
     * Method to execute on AJAX request timeout.
     *
     * @param Object oManipulateResponse Object to manipulate response.
     */
    onAjaxTimeout: function(oManipulateResponse, oForm)
    {
        oManipulateResponse.timeout(oForm);
    }
};