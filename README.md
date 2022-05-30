# Exercicio 2 de laravel

É outro blog, básicamente. Esta vez co tutorial de https://cosasdedevs.com/.

## Primeiro paso

O primeiro paso foi crear un proxecto de laravel co quickapp de Laragon. Logo instalamos todas as dependencias que necesitamos e tamén a de Tailwind, por suposto.

 Logo lanzamos o servidor co artisan para ver a web: php artisan web.

## Segundo paso

O seguinte é crear os modelos e migrar as tablas. Creamos unha base de datos en phpmyadmin e modificamos o arquivo .ENV introducindo os datos da base que acabamos de crear.

```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tutorial_laravel_blog
DB_USERNAME=root
DB_PASSWORD=mypass
```

Agora creamos os modelos na terminal: php artisan make:model Post -m

Vamos ao modelo de creación de tablas e lle poñemos a función pública para crear os campos:

```php
public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
```

Logo a función para os comentarios:

```php
public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('post_id')->constrained();

            $table->text('comment');

            $table->timestamps();
        });
    }
```

Por último tmeos que configurar os posts, para iso vamos a migración correspondente e creamos a funcion up:

```php
public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();

            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->mediumText('body');
            $table->boolean('is_draft')->default(false);

            $table->timestamps();
        });
    }
```

Toca crear datos para testear de mentira así que poñemos: php artisan make:factory PostFactory -m Post; e dentro do factory que acabamos de crear poñemos os datos de faker para crear posts falsos:

```php
<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'   => 1,
            'title'     => $this->faker->sentence,
            'body'      => $this->faker->text(3000),
        ];
    }
}
```

## Terceiro paso

O seguinte sería crear o controlador dos posts, que se pode facer facilmente co php artisan make:controller PostController --resource e engadirlle as funcións home() e detail()

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('posts/home', [
            'posts' => Post::where('is_draft', 0)->orderBy('created_at', 'desc')->get()->take(6)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function detail($slug)
    {
        $post = Post::where('slug', $slug)->where('is_draft', false)->first();
        abort_unless($post, 404);
        return view('posts/post', [
            'post' => $post
        ]);
    }
```

Agora vamos as nosas blades para configurar a plantilla que usaremos na app

, delimitando unha bvarra de navegación e un pé de páxina:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My Laravel Blog</title>
    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==" crossorigin="anonymous" />

</head>
<body>
    <header class="w-full">
        <nav class="w-full bg-orange-300 p-1 text-white flex justify-center">
            <div class="w-full flex justify-between px-4">
                @guest
                <ul class="flex justify-between" style="width:130px">
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('home') }}">
                            HOME
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i>
                        </a>
                    </li>
                </ul>
                @else
                <ul class="flex justify-between" style="width:140px">
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('home') }}">
                            HOME
                        </a>
                    </li>
                    <li>{{ Auth::user()->name }}</li>
                    @if( Auth::user()->isAdmin() or Auth::user()->isStaff() )
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('posts.store') }}" title="Admin">
                            <i class="fas fa-user-shield"></i>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="hover:text-blue-600" href="{{ route('logout') }}" title="logout" class="no-underline hover:underline" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i></a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            {{ csrf_field() }}
                        </form>
                    </li>
                </ul>
                @endguest
                <ul class="flex justify-between" style="width:99px">
                    <li>
                        <a class="hover:text-blue-600" href="http://">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-blue-600" href="http://">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </li>
                    <li>
                        <a class="hover:text-blue-600" href="http://">
                            <i class="fas fa-rss"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="text-center py-8 text-4xl font-bold">
            <h1>My Laravel Blog</h1>
        </div>
    </header>
    @yield('content')
    <footer class="mt-12">
        <div class="max-w-full bg-orange-300 p-4"></div>
        <div class="max-w-full text-center bg-gray-700 text-white p-4">
            <div class="text-lg font-bold">@MyLaravelBlog By <a class="hover:underline" href="https://cosasdedevs.com/" target="_blank">Alberto Ramírez</a></div>
        </div>
    </footer>
</body>
</html>
```

Imos as views e creamos unha blade par ao home agora:

```php
@extends('..layouts.app')

@section('content')
<section class="w-full bg-gray-200 py-4 flex-row justify-center text-center">
    <h2 class="py-4 text-3xl">About me</h2>
    <div class="flex text-justify justify-center">
        <div class="max-w-5xl px-2">
            Lorem ipsum dolor sit, amet consectetur adipisicing elit. Voluptate necessitatibus ullam commodi perferendis accusamus sint error sequi, dolorem nam, vel praesentium dignissimos nostrum quod fuga corporis asperiores laudantium, possimus veniam!
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur iure cumque qui impedit quod earum dolores nisi nemo totam vero natus aperiam, libero consequuntur nesciunt atque officia exercitationem rerum. Veritatis!
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptas in hic ratione recusandae nostrum, saepe aliquam alias ipsum? Asperiores rerum numquam officia harum atque, impedit perspiciatis facilis nobis tempora est!
        </div>
    </div>
</section>
<section class="w-full">
    <div class="flex justify-center">
        <div class="max-w-6xl text-center">
            <h2 class="py-4 text-3xl border-solid border-gray-300 border-b-2">Lasts posts</h2>
            <div class="flex flex-wrap justify-between">
                @foreach($posts as $post)
                <article style="width:300px" class="text-left p-2">
                    <h3 class="py-4 text-xl">{{$post->title}}</h3>
                    <p>{{$post->get_limit_body}} <a class="font-bold text-blue-600 no-underline hover:underline" href="{{ route('posts.detail', $post->slug) }}">Read more</a></p>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
```

E por último  outro para post.blade:

```php
@extends('..layouts.app')

@section('content')
<section class="w-full bg-gray-200 py-4 flex-row justify-center text-center">
    <div class="flex justify-center">
        <div class="max-w-4xl">
            <h1 class="px-4 text-6xl break-words">{{$post->title}}</h1>
        </div>
    </div>
</section>
<article class="w-full py-8">
    <div class="flex justify-center">
        <div class="max-w-4xl text-justify">
            {{$post->body}}
        </div>
    </div>
</article>
<section class="w-full py-8">
    <div class="max-w-4xl flex-row justify-start p-3 text-left ml-auto mr-auto border rounded shadow-sm bg-gray-50">
        <h3 class="py-4 text-2xl">Comments</h3>
        <div>
            @foreach($post->comments as $comment)
            <div class="w-full bg-white p-2 my-2 border">
                <div class="header flex justify-between mb-4 text-sm text-gray-500">
                    <div>
                        By {{$comment->user->name}}
                    </div>
                    <div>
                        {{$comment->created_at->format('j F, Y')}}
                    </div>
                </div>
                <div class="text-lg">{{$comment->comment}}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
```

Para que funcionen estas liñas debemos engadir as rutas no arquivo de routes:

```php
Route::get('/', [\App\Http\Controllers\PostController::class, 'home'])->name('home');
Route::get('/posts/{slug}', [\App\Http\Controllers\PostController::class, 'detail'])->name('posts.detail');
```

## Cuarto paso

Agora imos permitir a creación de comentarios creando a request que se chame CommentRequest (poñemos na terminal php artisan make:request Commentrequest)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => 'required|max:1000',
            'post_id' => 'exists:App\Models\Post,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'comment.required' => 'A comment is required',
            'comment.max' => 'A comment cannot exceed 1000 characters',
            'post_id.exists' => 'You must sent a valid post'
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('You must be logged in to write comments');
    }
}
```

E agora facemos un controlador para os comentarios (CommentController)

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\CommentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request)
    {
        $request->validated();

        $user = Auth::user();
        $post = Post::find($request->input('post_id'));

        $comment = new Comment;
        $comment->comment = $request->input('comment');
        $comment->user()->associate($user);
        $comment->post()->associate($post);

        $res = $comment->save();

        if ($res) {
            return back()->with('status', 'Comment has been created sucessfully');
        }

        return back()->withErrors(['msg', 'There was an error saving the comment, please try again later']);
    }
}
```

e engadimos a ruta para os comentarios

```php
Route::post('/comment', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
```

## Quinto paso

Neste paso faremos un nivel de seguridade admin, polo que engadimos esta ruta para os post visibles como admin:

```php
Route::resource('/admin/posts', \App\Http\Controllers\PostController::class);
```

Imos a PostController.php e mostramos o listado de posts coa función index:

```php
  /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless(Auth::check(), 404);
        $user = $request->user();

        if ($user->isAdmin()) {
            $posts = Post::orderBy('created_at', 'desc')->get();
        } elseif ($user->isStaff()) {
            $posts = Post::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        } else {
            abort_unless(Auth::check(), 404);
        }
        return view('posts/list', [
            'posts' => $posts
        ]);
    }
```

Agora que xa temos o controlador faremos un blade.list.php:

```php
@extends('..layouts.app')

@section('content')
<section class="w-full bg-gray-200 py-4 flex-row justify-center text-center">
    <div class="flex justify-center">
        <div class="max-w-4xl">
            <h1 class="px-4 text-6xl break-words">List Post</h1>
        </div>
    </div>
</section>
<article class="w-full py-8">
    <div class="flex justify-center">
        <div class="max-w-7xl text-justify">@if($errors->any())
            <div class="w-full bg-red-500 p-2 text-center my-2 text-white">
                {{$errors->first()}}
            </div>
            @endif
            @if (session('status'))
                <div class="w-full bg-green-500 p-2 text-center my-2 text-white">
                    {{ session('status') }}
                </div>
            @endif
            <div class="text-right py-2">
                <a class="inline-block px-4 py-1 bg-orange-500 text-white rounded mr-2 hover:bg-orange-800" href="{{ route('posts.create') }}" title="Edit">Create new post</a>
            </div>
            <table class="table-auto">
                <thead>
                    <tr>
                        <th class="px-2">Title</th>
                        <th class="px-2">Creation</th>
                        <th class="px-2">Author</th>
                        <th class="px-2">Status</th>
                        <th class="px-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td class="px-2">{{ $post->title }}</td>
                        <td class="px-2">{{ $post->created_at->format('j F, Y') }}</td>
                        <td class="px-2">{{ $post->user->name }}</td>
                        <td class="px-2">
                            @if ($post->is_draft)
                                <div class="text-red-500">In draft</div>
                            @else
                                <div class="text-green-500">Published</div>
                            @endif
                        </td>
                        <td class="px-2">
                            <a class="inline-block px-4 py-1 bg-blue-500 text-white rounded mr-2 hover:bg-blue-800" href="{{ route('posts.edit', $post) }}" title="Edit">Edit</a>

                            <a class="inline-block px-4 py-1 bg-red-500 text-white rounded mr-2 hover:bg-red-800 delete-post" href="{{ route('posts.destroy', $post) }}" title="Delete" data-id="{{$post->id}}">Delete</a>
                            <form id="posts.destroy-form-{{$post->id}}" action="{{ route('posts.destroy', $post) }}" method="POST" class="hidden">
                                {{ csrf_field() }}
                                @method('DELETE')
                            </form>
                        </td>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</article>
<script>

    var delete_post_action = document.getElementsByClassName("delete-post");

    var deleteAction = function(e) {
        event.preventDefault();
        var id = this.dataset.id;
        if(confirm('Are you sure?')) {
            document.getElementById('posts.destroy-form-' + id).submit();
        }
        return false;
    }

    for (var i = 0; i < delete_post_action.length; i++) {
        delete_post_action[i].addEventListener('click', deleteAction, false);
    }
</script>
@endsection
```

Para crear os post engadimos a función create() no PostController.php:

```php
  /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless(Auth::check(), 404);
        $request->user()->authorizeRoles(['is_staff', 'is_admin']);
        return view('posts/create');
    }
```

E facemos o blade de create.blade.php

```php
@extends('..layouts.app')

@section('content')
<section class="w-full bg-gray-200 py-4 flex-row justify-center text-center">
    <div class="flex justify-center">
        <div class="max-w-4xl">
            <h1 class="px-4 text-6xl break-words">Create Post</h1>
        </div>
    </div>
</section>
<article class="w-full py-8">
    <div class="flex justify-center">
        <div class="max-w-7xl text-justify">
            <form action="{{ route('posts.store') }}" method="post">
                @csrf
                <input class="w-full border rounded focus:outline-none focus:shadow-outline p-2 mb-4" type="text" name="title" value="{{ old('title') }}" placeholder="Write the title of the post">
                <textarea class="w-full h-72 resize-none border rounded focus:outline-none focus:shadow-outline p-2 mb-4" name="body" placeholder="Write your post here" required>{{ old('body') }}</textarea>
                <div class="mb-4">
                    <input type="hidden" name="is_draft" value="0">
                    <input type="checkbox" name="is_draft" value="1"> Is draft?
                </div>
                <input type="submit" value="SEND" class="px-4 py-2 bg-orange-300 cursor-pointer hover:bg-orange-500 font-bold w-full border rounded border-orange-300 hover:border-orange-500 text-white">
                @if (session('status'))
                    <div class="w-full bg-green-500 p-2 text-center my-2 text-white">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                <div class="w-full bg-red-500 p-2 text-center my-2 text-white">
                    {{$errors->first()}}
                </div>
                @endif
            </form>
        </div>
    </div>
</article>
@endsection
```

Creamos o request para facer post poñendo na terminal php artisan make:request PostRequest e lle engadimos o seguinte código.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
            'is_draft' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'A title is required',
            'title.max' => 'A title cannot exceed 255 characters',
            'body.required' => 'You must sent a body',
            'is_draft.required' => 'You must sent if is draft or not',
        ];
    }
}
```

Volvemos a PostController.php e lle engadimos este código no método store():

```php
/**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $request->validated();
        $user = Auth::user();

        $request->user()->authorizeRoles(['is_staff', 'is_admin']);

        $post = new Post;
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->is_draft = $request->input('is_draft');
        $post->user()->associate($user);

        $res = $post->save();

        if ($res) {
            return back()->with('status', 'Post has been created sucessfully');
        }

        return back()->withErrors(['msg', 'There was an error saving the post, please try again later']);
    }
```

Logo, para actualizar os posts, volvemos a post controller e facemos o método edit():

```php
/**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        abort_unless(Auth::check(), 404);
        $request->user()->authorizeRoles(['is_staff', 'is_admin']);
        $post = Post::find($id);
        if (($post->user->id != $request->user()->id) && !$request->user()->isAdmin()) {
            abort_unless(false, 401);
        }
        return view('posts/edit', [
            'post' => $post
        ]);
    }
```

E creamos a sua blade correspondente:

```html
@extends('..layouts.app')

@section('content')
<section class="w-full bg-gray-200 py-4 flex-row justify-center text-center">
    <div class="flex justify-center">
        <div class="max-w-4xl">
            <h1 class="px-4 text-6xl break-words">Edit Post</h1>
        </div>
    </div>
</section>
<article class="w-full py-8">
    <div class="flex justify-center">
        <div class="max-w-7xl text-justify">
            <form action="{{ route('posts.update', $post) }}" method="post">
                @csrf
                @method('PUT')
                <input class="w-full border rounded focus:outline-none focus:shadow-outline p-2 mb-4" type="text" name="title" value="{{ $post->title }}" placeholder="Write the title of the post">
                <textarea class="w-full h-72 resize-none border rounded focus:outline-none focus:shadow-outline p-2 mb-4" name="body" placeholder="Write your post here" required>{{ $post->body }}</textarea>
                <div class="mb-4">
                    <input type="hidden" name="is_draft" value="0">
                    @if (!$post->is_draft)
                        <input type="checkbox" name="is_draft" value="1">
                    @else
                        <input type="checkbox" name="is_draft" value="1" checked>
                    @endif
                    Is draft?
                </div>
                <input type="submit" value="SEND" class="px-4 py-2 bg-orange-300 cursor-pointer hover:bg-orange-500 font-bold w-full border rounded border-orange-300 hover:border-orange-500 text-white">
                @if (session('status'))
                    <div class="w-full bg-green-500 p-2 text-center my-2 text-white">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                <div class="w-full bg-red-500 p-2 text-center my-2 text-white">
                    {{$errors->first()}}
                </div>
                @endif
            </form>
        </div>
    </div>
</article>
@endsection
```

Agora facemos o mesmo pero para borrar posts, creamos o método destroy() no PostController.php:

```php
/**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        abort_unless(Auth::check(), 404);
        $request->user()->authorizeRoles(['is_staff', 'is_admin']);
        $post = Post::where('id', $id)->first();
        if (($post->user->id != $request->user()->id) && !$request->user()->isAdmin()) {
            abort_unless(false, 401);
        }

        $post->delete();

        return back()->with('status', 'Post has been deleted sucessfully');
    }
```

## Sexto paso



Agora vamos a faver test unitarios, primeiro copiando o .env e facendo unha copia e logo metendo unha base de datos de proba nel. Tamén cambiamos os parámetros de Factory para que os nosos usuarios sempre teñan permiso de admin.

Creamos un arquivo para testear comentarios poñendo na terminal 

```bash
php artisan make:test CommentTest
```

Que teña este código:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class CommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCreateComment()
    {
        $user = User::factory()->create();
        $test_post = [
            'title' => 'My test post with comment',
            'body' => 'This is a test functional post',
            'is_draft' => false
        ];
        $response = $this->actingAs($user)->post('/admin/posts', $test_post);
        $response->assertSessionHas('status', 'Post has been created sucessfully');

        $post = Post::where('title', $test_post['title'])->first();

        $comment = 'This is a test comment';
        $test_comment = [
            'post_id' => $post->id,
            'comment' => $comment
        ];
        $response = $this->actingAs($user)->post('/comment', $test_comment);
        $response->assertSessionHas('status', 'Comment has been created sucessfully');

        $comment = Comment::where('user_id', $user->id)
        ->where('post_id', $post->id)
        ->where('comment', $comment)->first();

        $this->assertNotNull($comment);

        $this->post('/logout');

        // Ahora probamos con un usuario sin loguear
        $response = $this->post('/comment', $test_comment);
        $response->assertStatus(403);
    }
}
```

E lanzamos o test cun simple php artisan test. Se todo vai ben nos indicará que non hai fallos e poderemos dar por concluído o exercicio.
