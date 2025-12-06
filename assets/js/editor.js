/* global wp */
(function () {
    if (!wp || !wp.plugins || !wp.editPost) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost;
    const { createElement: el } = wp.element;
    const { PanelBody } = wp.components;

    const SidebarContent = () =>
        el(
            PanelBody,
            { title: "AP Internal Linking Helper", initialOpen: true },
            el("p", null, "Sidebar placeholder loaded.")
        );

    registerPlugin("apilh-sidebar-placeholder", {
        render: () =>
            el(
                PluginSidebar,
                {
                    name: "apilh-sidebar",
                    title: "AP Link Helper",
                    icon: "admin-links",
                },
                el(SidebarContent)
            ),
    });
})();
