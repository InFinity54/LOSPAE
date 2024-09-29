$("#schoolglobalselection").change((event) => {
    if($("#schoolglobalselection").is(":checked")) {
        $(".schoolselection").prop("checked", true);
    } else {
        $(".schoolselection").prop("checked", false);
    }
})

$("#delete_selected_schools").on("click", () => {
    handleAction("remove");
});

function handleAction(actionName) {
    let ids = [];

    $(".schoolselection:checked").each(function () {
        ids.push($(this).data("school"));
    });

    window.location.href = window.location.origin + "/admin/schools/" + actionName + "/" + ids.join(",");
}