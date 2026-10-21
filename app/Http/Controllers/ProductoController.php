<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Menú (carta) del restaurante. */
class ProductoController extends Controller
{
    public function index(): View
    {
        $productos = Producto::orderBy('categoria')->orderBy('nombre')->get()->groupBy('categoria');

        return view('productos.index', compact('productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $producto = Producto::create($this->validar($request));
        Auditoria::registrar('crear_producto', 'productos', $producto->id, $producto->nombre);

        return back()->with('status', 'Producto agregado al menú.');
    }

    public function update(Producto $producto, Request $request): RedirectResponse
    {
        $producto->update($this->validar($request));
        Auditoria::registrar('editar_producto', 'productos', $producto->id, $producto->nombre);

        return back()->with('status', 'Producto actualizado.');
    }

    public function alternar(Producto $producto): RedirectResponse
    {
        $producto->update(['disponible' => ! $producto->disponible]);

        return back()->with('status', $producto->nombre.($producto->disponible ? ' disponible.' : ' agotado.'));
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', Rule::in(['desayuno', 'entrada', 'plato_fuerte', 'bebida', 'postre', 'snack'])],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
