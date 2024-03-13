function handleCodeMailResend() {
    const resendButton = $("#resend");
    const resendSpinner = $("#resend_spinner");
    const resendText = $("#resend_text");

    resendButton.prop('disabled', true);
    resendSpinner.show();
    resendText.hide();

    $.ajax({
        url: "/passwordrecover/resend/" + $("#user").val(),
        cache: false,
        success: (data, textStatus, jqXHR) => {
            console.log(data, textStatus, jqXHR);
        },
        error: (qXHR, textStatus, errorThrown) => {
            console.error(qXHR, textStatus, errorThrown);
        },
        complete: () => {
            resendSpinner.hide();
            resendText.show();

            setTimeout(() => {
                resendButton.prop('disabled', false);
            }, 3000);
        }
    });
}