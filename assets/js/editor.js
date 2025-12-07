/* global wp */
(function () {
    if (!wp || !wp.plugins || !wp.editPost) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost;
    const { createElement: el, useEffect, useState } = wp.element;
    const { PanelBody, Button, Spinner, Notice } = wp.components;
    const { select } = wp.data;
    const apiFetch = wp.apiFetch;

    function SidebarContent() {
        const [items, setItems] = useState([]);
        const [loading, setLoading] = useState(false);
        const [error, setError] = useState("");

        const postId = select("core/editor")?.getCurrentPostId?.() || 0;

        const loadSuggestions = () => {
            setLoading(true);
            setError("");

            apiFetch({ path: `/apilh/v1/suggestions?post_id=${postId}` })
                .then((res) => {
                    setItems(res?.items || []);
                })
                .catch(() => {
                    setError("Could not load suggestions.");
                    setItems([]);
                })
                .finally(() => {
                    setLoading(false);
                });
        };

        useEffect(() => {
            loadSuggestions();
            // eslint-disable-next-line react-hooks/exhaustive-deps
        }, [postId]);

        return el(
            PanelBody,
            { title: "AP Internal Linking Helper", initialOpen: true },

            error
                ? el(Notice, { status: "error", isDismissible: false }, error)
                : null,

            loading ? el(Spinner, null) : null,

            !loading && items.length === 0
                ? el("p", null, "No suggestions yet.")
                : null,

            !loading && items.length > 0
                ? el(
                      "ul",
                      { style: { marginLeft: "1.2em" } },
                      items.map((item) =>
                          el(
                              "li",
                              { key: item.id },
                              el(
                                  "a",
                                  {
                                      href: item.link,
                                      target: "_blank",
                                      rel: "noopener noreferrer",
                                  },
                                  item.title || "(Untitled)"
                              ),
                              el(
                                  "span",
                                  {
                                      style: {
                                          opacity: 0.6,
                                          marginLeft: "6px",
                                          fontSize: "11px",
                                      },
                                  },
                                  item.type,
                                  item.same_category ? " • same category" : "",
                                  item.same_tag ? " • same tag" : ""
                              )
                          )
                      )
                  )
                : null,

            el(
                "div",
                { style: { marginTop: "12px" } },
                el(
                    Button,
                    {
                        variant: "secondary",
                        onClick: loadSuggestions,
                        disabled: loading,
                    },
                    "Refresh"
                )
            )
        );
    }

    registerPlugin("apilh-sidebar", {
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
