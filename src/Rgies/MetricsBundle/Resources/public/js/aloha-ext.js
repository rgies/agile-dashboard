function aloha_ext_init()
{
    // add delete event in embedded images
    $(function() {
        $(document).on("dblclick",".aloha-image-box-active", function(event) {
            if (confirm("Delete Image?")) {
                event.preventDefault();
                $(this).remove();
            }
            return false;
        });
    });

    var image_library_panel = Aloha.Sidebar.right.addPanel({
        // the id of your new Panel
        id: "image-library-panel",
        // title to be set for your Panel
        title: "Image Library",
        // initial html content of your panel
        content: '<div id="aloha-image-upload"></div>',
        // whether the panel should be expanded initially
        expanded: true
    });

}