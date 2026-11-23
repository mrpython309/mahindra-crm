@php
  use Illuminate\Support\Facades\Route;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <!-- <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('variables.templateName') }}</span> -->
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>

  <div class="menu-divider mt-0"></div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)
      {{-- adding active and open class if child is active --}}

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
      @else
        {{-- permission check: if slug present, require '<slug>:view' permission to show. For parents, show if any child visible. --}}
        @php
          $showMenu = true;
          if (isset($menu->slug) && !empty($menu->slug)) {
              if (is_array($menu->slug)) {
                  // If slug is array, check if user has permission for any of the slugs
                  $showMenu = false;
                  if (auth()->check()) {
                      if (auth()->user()->hasRole('superadmin')) {
                          $showMenu = true;
                      } else {
                          foreach ($menu->slug as $slug) {
                              if (auth()->user()->can($slug . ':view')) {
                                  $showMenu = true;
                                  break;
                              }
                          }
                      }
                  }
              } else {
                  // Single slug
                  if (auth()->check()) {
                      $showMenu = auth()->user()->hasRole('superadmin') || auth()->user()->can($menu->slug . ':view');
                  } else {
                      $showMenu = false;
                  }
              }
          }

          // If menu has submenu, check if any child is visible
          $hasVisibleChild = false;
          if (isset($menu->submenu) && is_array($menu->submenu)) {
              foreach ($menu->submenu as $child) {
                  $childPerm = null;
                  if (isset($child->permission) && !empty($child->permission)) {
                      $childPerm = $child->permission;
                  } elseif (isset($child->slug) && !empty($child->slug)) {
                      $childPerm = is_array($child->slug)
                          ? null
                          : ($child->slug . ':view');
                  }
                  if (
                      !$childPerm ||
                      (auth()->check() && (auth()->user()->hasRole('superadmin') || auth()->user()->can($childPerm)))
                  ) {
                      $hasVisibleChild = true;
                      break;
                  }
              }
          }

          if (!$showMenu && !$hasVisibleChild) {
              continue; // skip rendering this menu
          }

        @endphp

        {{-- active menu method --}}
        @php
          $activeClass = '';
          $currentRouteName = Route::currentRouteName();
          $slugs = isset($menu->slug)
              ? (is_array($menu->slug) ? $menu->slug : [$menu->slug])
              : [];

          foreach ($slugs as $slug) {
              if ($currentRouteName === $slug || str_starts_with($currentRouteName, $slug)) {
                  if ($slug === 'employee' && $currentRouteName === 'employee.hierarchy') {
                      continue;
                  }
                  $activeClass = isset($menu->submenu) ? 'active open' : 'active';
                  break;
              }
          }

          if (empty($activeClass) && isset($menu->submenu)) {
              foreach ($menu->submenu as $child) {
                  if (isset($child->slug) && ($currentRouteName === $child->slug || str_starts_with($currentRouteName, $child->slug))) {
                      if ($child->slug === 'employee' && $currentRouteName === 'employee.hierarchy') {
                          continue;
                      }
                      $activeClass = 'active open';
                      break;
                  }
              }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
            @isset($menu->badge)
              <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}</div>
            @endisset
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
    @endforeach
  </ul>

</aside>
