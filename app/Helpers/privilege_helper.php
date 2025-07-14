function hasPrivilege($submenuName, $action)
{
    $privileges = session()->get('privileges');
    if (! $privileges) return false;

    foreach ($privileges as $priv) {
        if ($priv['submenu_name'] === $submenuName && !empty($priv[$action]) && $priv[$action] == 1) {
            return true;
        }
    }
    return false;
}
