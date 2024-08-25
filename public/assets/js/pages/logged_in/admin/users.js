$("#enable_selected_users").on("click", () => {
    let ids = [];

    $(".userselection:checked").each(function () {
        ids.push($(this).data("user"));
    });

    window.location.href = window.location.origin + "/admin/users/enable/" + ids.join(",");
});