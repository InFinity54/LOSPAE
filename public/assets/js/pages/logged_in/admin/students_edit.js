$("#school").on("change", () => {
    $.ajax({
        url: "/admin/school/" + $("#school").val() + "/promos",
        dataType: "json",
        success: function (data, textStatus, jqXHR) {
            for (const promo in data) {
                $("#promo").append(`<option value="${data[promo].id}">${data[promo].name}</option>`);
            }
        },
        error: function (qXHR, textStatus, errorThrown) {
            console.error(errorThrown);
        },
    });
});