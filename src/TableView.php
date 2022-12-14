<?php

namespace Share;

use Dynart\Micro\View;

class TableView {
    
    private $view;
    private $route;
    private $params;
    private $columns;
    private $items;
    private $actions = [];
    private $groupActions = [];
    private $selectable = 'selected';

    public function __construct(View $view, string $route, array $params) {
        $this->view = $view;
        $this->route = $route;
        $this->params = $params;
    }

    public function addGroupAction(string $name, string $label) {
        $this->groupActions[$name] = $label;
    }

    public function setColumns(array $columns) {
        $this->columns = $columns;
    }

    public function setItems(array $items) {
        $this->items = $items;
    }

    public function addAction(string $route, string $label) {
        $this->actions[$route] = $label;
    }

    public function setSelectable(bool $value) {
        $this->selectable = $value;
    }

    public function checkView(string $field, array $item) {
        return $item[$field] ? '&#10004;' : '';
    }

    public function fetch() {
        $html = '<div class="table-view">';
        $html .= $this->fetchGroupActionForm();
        $html .= '<table>';
        $html .= $this->fetchHead();
        $html .= $this->fetchBody();
        $html .= '</table>';
        $html .= $this->fetchGroupActionFormEnd();
        $html .= '</div>';
        return $html;
    }

    private function fetchGroupActionForm() {
        if (!$this->groupActions) {
            return '';
        }
        $html = '<form action="'.$this->view->routeUrl($this->route).'" method="POST">';
        $html .= '<div class="table-view-group-action-form">';
        $html .= '<select id="group_action_select" name="group_action">';
        $html .= '<option>-- Do nothing --</option>';
        foreach ($this->groupActions as $name => $label) {
            $html .= '<option value="'.$name.'">'.$this->view->escape($label).'</option>';
        }
        $html .= '</select>';
        $html .= '<input type="submit" value="Submit">';
        foreach ($this->params as $name => $value) {
            if ($value === null) continue;
            $html .= '<input type="hidden" name="'.$name.'" value="'.$this->view->escapeAttribute($value).'">';
        }
        $html .= '</div>';
        return $html;
    }

    private function fetchGroupActionFormEnd() {
        return $this->groupActions ? '</form>' : '';
    }

    private function fetchHead() {
        $html = '<thead><tr>';
        if ($this->selectable) {
            $html .= '<th></th>';
        }
        if ($this->actions) {
            $html .= '<th></th>';
        }
        foreach ($this->columns as $field => $column) {
            $style = isset($column['width']) ? ' style="width: '.$column['width'].'"' : '';
            $html .= '<th'.$style.'>';

            // change values in params for order by/dir for the header link
            $params = $this->params;
            $isCurrent = isset($params['order_by']) && $params['order_by'] == $field;
            $params['order_dir'] = $isCurrent && $params['order_dir'] == 'asc' ? 'desc' : 'asc';
            $params['order_by'] = $field;

            // create the link (put in a table because the order icon breaks on small screens)
            $html .= '<a href="'.$this->view->routeUrl($this->route, $params).'">';
            $html .= '<table><tr>';
            $html .= '<td>'.$this->nbspView($column['label']).'</td>';
            if ($isCurrent) {
                $html .= '<td><span>'.($params['order_dir'] == 'asc' ? '&#9660;' : '&#9650;').'</span></td>';
            }
            $html .= '</tr></table>';
            $html .= '</a>';

            $html .= '</th>'."\n";
        }
        $html .= '</tr></thead>';
        return $html;
    }

    private function fetchBody() {
        $html = '<tbody>';
        foreach ($this->items as $item) {
            $html .= '<tr>'."\n";
            $html .= $this->fetchCheckbox($item);
            $html .= $this->fetchActions($item);
            $html .= $this->fetchRow($item);
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        return $html;
    }

    private function fetchCheckbox($item) {
        if (!$this->selectable) {
            return '';
        }
        return '<td><input name="'.$this->selectable.'[]" type="checkbox" value="'.$item['id'].'"></td>';
    }

    private function fetchActions(array $item) {
        if (!$this->actions) {
            return '';
        }
        $actions = [];
        foreach ($this->actions as $route => $label) {
            $url = $this->view->routeUrl($route.'/'.$item['id']);
            $actions[] = '<a href="'.$url.'">'.$this->nbspView($label).'</a>';
        }
        // must be in a table because of the line breaks on small screen
        return '<td><table><tr><td>'.join('</td><td>', $actions).'</td></tr></table></td>'."\n";
    }

    private function fetchRow(array $item) {
        $html = '';
        foreach ($this->columns as $field => $column) {
            $html .= '<td>';
            if (isset($column['view'])) {
                $html .= call_user_func_array($column['view'], [$field, $item]);
            } else {
                $html .= $this->nbspView($item[$field]);
            }
            $html .= '</td>'."\n";
        }
        return $html;
    }

    private function nbspView($value) {
        return str_replace(' ', '&nbsp;', $this->view->escape($value));
    }

}