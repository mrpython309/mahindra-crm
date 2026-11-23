@php
  use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      {{-- permission check: if slug present, user must have '{slug}:view' to see it --}}
      @php
        $canView = true;

        if (isset($submenu->permission) && !empty($submenu->permission)) {
            $perm = $submenu->permission;
            $canView = auth()->check()
                ? auth()->user()->hasRole('superadmin') || auth()->user()->can($perm)
                : false;
        } elseif (isset($submenu->slug) && !empty($submenu->slug)) {
            if (is_array($submenu->slug)) {
                $canView = false;
                foreach ($submenu->slug as $slug) {
                    $perm = $slug . ':view';
                    if (auth()->check() && (auth()->user()->hasRole('superadmin') || auth()->user()->can($perm))) {
                        $canView = true;
                        break;
                    }
                }
            } else {
                $perm = $submenu->slug . ':view';
                $canView = auth()->check()
                    ? auth()->user()->hasRole('superadmin') || auth()->user()->can($perm)
                    : false;
            }
        }

        // If there are children, we'll render parent if any child is visible
        $hasVisibleChild = false;
        if (isset($submenu->submenu) && is_array($submenu->submenu)) {
          foreach ($submenu->submenu as $child) {
            if (isset($child->permission) && !empty($child->permission)) {
              $childPerm = $child->permission;
            } elseif (isset($child->slug) && !empty($child->slug)) {
              $childPerm = $child->slug . ':view';
            } else {
              $childPerm = null;
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
      @endphp

      @if (!$canView && !$hasVisibleChild)
        @continue
      @endif

      {{-- active menu method --}}
      @php
        $activeClass = '';
        $currentRouteName = Route::currentRouteName();
        $slugs = isset($submenu->slug)
            ? (is_array($submenu->slug) ? $submenu->slug : [$submenu->slug])
            : [];

        foreach ($slugs as $slug) {
            // Matches exact route name or any route starting with the slug
            if ($currentRouteName === $slug || str_starts_with($currentRouteName, $slug)) {
                if ($slug === 'employee' && $currentRouteName === 'employee.hierarchy') {
                    continue;
                }
                $activeClass = 'active';
                break;
            }
        }

        // If this submenu has its own children, check them too
        if (empty($activeClass) && isset($submenu->submenu)) {
            foreach ($submenu->submenu as $child) {
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

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
          class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
          @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge rounded-pill bg-{{ $submenu->badge[0] }} text-uppercase ms-auto">{{ $submenu->badge[1] }}
            </div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
