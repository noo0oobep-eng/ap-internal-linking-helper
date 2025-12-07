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
        const [copiedId, setCopiedId] = useState(0);

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

        const copyLink = async (item) => {
            try {
                if (navigator?.clipboard?.writeText) {
                    await navigator.clipboard.writeText(item.link);
                } else {
                    const ta = document.createElement("textarea");
                    ta.value = item.link;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand("copy");
                    document.body.removeChild(ta);
                }
                setCopiedId(item.id);
                window.setTimeout(() => setCopiedId(0), 1200);
            } catch (e) {
                setError("Copy failed.");
            }
        };

        useEffect(() => {
            loadSuggestions();
            // eslint-disable-next-line react-hooks/exhaustive-deps
        }, [postId]);

        return el(
            PanelBody,
            { title: "AP Internal Linking Helper", initialOpen: true },

            error ? el(Notice, { status: "error", isDismissible: false }, error) : null,
            loading ? el(Spinner, null) : null,

            !loading && items.length === 0 ? el("p", null, "No suggestions yet.") : null,

            !loading && items.length > 0
                ? el(
                      "div",
                      null,
                      items.map((item) =>
                          el(
                              "div",
                              {
                                  key: item.id,
                                  style: {
                                      padding: "8px 0",
                                      borderBottom: "1px solid rgba(0,0,0,0.06)",
                                  },
                              },
                              el(
                                  "div",
                                  { style: { marginBottom: "6px" } },
                                  el(
                                      "a",
                                      {
                                          href: item.link,
                                          target: "_blank",
                                          rel: "noopener noreferrer",
                                          style: { fontWeight: 600 },
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
                              ),
                              el(
                                  "div",
                                  { style: { display: "flex", gap: "6px" } },
                                  el(
                                      Button,
                                      {
                                          variant: "secondary",
                                          size: "small",
                                          onClick: () => copyLink(item),
                                      },
                                      copiedId === item.id ? "Copied" : "Copy link"
                                  ),
                                  el(
                                      Button,
                                      {
                                          variant: "tertiary",
                                          size: "small",
                                          onClick: () => window.open(item.link, "_blank", "noopener"),
                                      },
                                      "Open"
                                  )
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
                    { variant: "secondary", onClick: loadSuggestions, disabled: loading },
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
