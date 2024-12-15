$("#promosglobalselection").change((event) => {
    if($("#promosglobalselection").is(":checked")) {
        $(".promoselection").prop("checked", true);
    } else {
        $(".promoselection").prop("checked", false);
    }
})