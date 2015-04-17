CREATE INDEX showinsummary ON `#__fabrik_elements` (show_in_list_summary);
CREATE INDEX plugin ON `#__fabrik_elements` (plugin(10));
CREATE INDEX join_checked_out ON `#__fabrik_elements` (checked_out);
CREATE INDEX join_group_id ON `#__fabrik_elements` (group_id);
CREATE INDEX parent_id ON `#__fabrik_elements` (parent_id);

CREATE INDEX join_group_id ON `#__fabrik_formgroup` (group_id);
CREATE INDEX join_form_id ON `#__fabrik_formgroup` (form_id);
CREATE INDEX ordering ON `#__fabrik_formgroup` (ordering);

CREATE INDEX published ON `#__fabrik_groups` (published);

CREATE INDEX list_id ON `#__fabrik_joins` (list_id);
CREATE INDEX element_id ON `#__fabrik_joins` (element_id);
CREATE INDEX group_id ON `#__fabrik_joins` (group_id);
CREATE INDEX table_join ON `#__fabrik_joins` (table_join(100));

CREATE INDEX published ON `#__fabrik_forms` (published);
CREATE INDEX form_id ON `#__fabrik_lists` (form_id);

CREATE INDEX element_id ON `#__fabrik_jsactions` (element_id);
