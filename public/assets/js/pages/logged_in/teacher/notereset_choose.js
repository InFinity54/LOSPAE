$("#studentsglobalselection").change((event) => {
    if($("#studentsglobalselection").is(":checked")) {
        $(".studentselection").prop("checked", true);
    } else {
        $(".studentselection").prop("checked", false);
    }
})