$("#teacherglobalselection").change((event) => {
    if($("#teacherglobalselection").is(":checked")) {
        $(".teacherselection").prop("checked", true);
    } else {
        $(".teacherselection").prop("checked", false);
    }
})

$("#enable_selected_teachers").on("click", () => {
    handleAction("enable");
});

$("#disable_selected_teachers").on("click", () => {
    handleAction("disable");
});

$("#delete_selected_teachers").on("click", () => {
    handleAction("remove");
});

function handleAction(actionName) {
    let ids = [];

    $(".teacherselection:checked").each(function () {
        ids.push($(this).data("teacher"));
    });

    window.location.href = window.location.origin + "/admin/teachers/" + actionName + "/" + ids.join(",");
}