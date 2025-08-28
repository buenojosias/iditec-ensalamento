<ul class="internal-menu">
    <li><a href="{{ route('teams.index') }}" class="{{ request()->routeIs('teams.index') ? 'active' : '' }}">Atuais</a></li>
    <li><a href="{{ route('teams.next') }}" class="{{ request()->routeIs('teams.next') ? 'active' : '' }}">Próximas</a></li>
    <li><a href="{{ route('teams.import') }}" class="{{ request()->routeIs('teams.import') ? 'active' : '' }}">Importar</a></li>
</ul>
