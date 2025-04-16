@php
        $scopes = implode(' ', [
            'contacts.readonly',
            'locations.readonly',
            'oauth.write',
            'oauth.readonly'
        ]);

        $href = 'https://marketplace.gohighlevel.com/oauth/chooselocation?response_type=code&redirect_uri=' 
            . route('authorization.gohighlevel.callback') 
            . '&client_id=' . env('GHL_CLIENT_ID') 
            . '&scope=' . urlencode($scopes);
            
            $description = 'Connect to GoHighLevel';
    @endphp
    <a href="{{ $href }}" class="menu-link px-3">
        <img src="{{ asset('ghl_icon.jpeg') }}" alt="Logo" class="h-30px mx-3" />
        <span class="menu-title fs-5">{{ $description }}</span>
    </a>


                    