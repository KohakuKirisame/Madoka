<nav class="navbar navbar-expand-lg sticky-top bg-primary shadow-lg navbar-dark">
	<div class="container-fluid">
		<a class="navbar-brand" href="/"><img src="{{asset("storage/img/Madoka.svg")}}" style="height: 48px;" /></a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse justify-content-between" id="navbarSupportedContent">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link" aria-current="page" href="/Dashboard">DashBoard</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/Map">星图</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/Planets">星球</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/Military">军事</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/ForeignAffairs">外交</a>
				</li>
                <li class="nav-item">
                    <a class="nav-link" href="/News">舆情</a>
                </li>
				<!--<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Dropdown
					</a>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">Action</a></li>
						<li><a class="dropdown-item" href="#">Another action</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="#">Something else here</a></li>
					</ul>
				</li>
				<li class="nav-item">
					<a class="nav-link disabled">Disabled</a>
				</li>-->
			</ul>
            <div class="d-flex me-4">
                <li class="nav-item dropdown" style="list-style: none">
                    <a class="nav-link dropdown-toggle text-white px-3 py-3" href="#" id="user" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{$user["name"]}}</a>
                    <ul class="dropdown-menu" aria-labelledby="user">
                        <li><a class="dropdown-item" href="{{$_ENV["REIMU_URL"]}}">个人资料</a></li>
                        <li><a class="dropdown-item" href="/Action/Logout">登出</a></li>
                    </ul>
                </li>
                <img src="{{$_ENV["REIMU_URL"]}}/storage/avatar/{{$user["avatar"]}}" class="rounded-circle" style="height: 48px" />
            </div>

		</div>
	</div>
</nav>
