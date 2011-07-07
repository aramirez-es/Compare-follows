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
    handleSent: function(sClassName, oManipulateResponse)
    {
        var self = this;

        $("form." + sClassName).each(function(){
            $(this).submit(function(eEvent)
            {
                var oForm = eEvent.target;

                jQuery.ajax(
                {
                    url: $(oForm).attr("action"),
                    async: true,
                    type: "POST",
                    data: $(oForm).serialize(),
                    success: function(oReponse)
                    {
                        return self.onAjaxSuccess(oManipulateResponse, oReponse);
                    },
                    error: function()
                    {
                        return self.onAjaxError(oManipulateResponse);
                    },
                    timeout: function()
                    {
                        return self.onAjaxTimeout(oManipulateResponse);
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
    onAjaxSuccess: function(oManipulateResponse, oReponse)
    {
        oManipulateResponse.success(oReponse);
    },

    /**
     * Method to execute on AJAX request error.
     *
     * @param Object oManipulateResponse Object to manipulate response.
     */
    onAjaxError: function(oManipulateResponse)
    {
        oManipulateResponse.error();
    },

    /**
     * Method to execute on AJAX request timeout.
     *
     * @param Object oManipulateResponse Object to manipulate response.
     */
    onAjaxTimeout: function(oManipulateResponse)
    {
        oManipulateResponse.timeout();
    }
};