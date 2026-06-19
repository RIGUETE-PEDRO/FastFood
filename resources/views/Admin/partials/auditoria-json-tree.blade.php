@foreach($dados as $chave => $valor)
    @php
        $temFilhos = is_array($valor) || is_object($valor);
        $filhos = is_object($valor) ? (array) $valor : $valor;
        $rotulo = is_int($chave) ? '[' . $chave . ']' : $chave;
    @endphp

    @if($temFilhos)
        <details class="auditoria-json-tree__branch" open>
            <summary>
                <span class="auditoria-json-tree__key">{{ $rotulo }}</span>
                <span class="auditoria-json-tree__type">
                    {{ array_is_list($filhos) ? '[' . count($filhos) . ' itens]' : '{' . count($filhos) . ' campos}' }}
                </span>
            </summary>
            <div class="auditoria-json-tree__children">
                @if(count($filhos))
                    @include('Admin.partials.auditoria-json-tree', ['dados' => $filhos])
                @else
                    <div class="auditoria-json-tree__row">
                        <span class="auditoria-json-tree__empty">{{ array_is_list($filhos) ? '[]' : '{}' }}</span>
                    </div>
                @endif
            </div>
        </details>
    @else
        <div class="auditoria-json-tree__row">
            <span class="auditoria-json-tree__key">{{ $rotulo }}</span>
            <span class="auditoria-json-tree__separator">:</span>

            @if(is_bool($valor))
                <span class="auditoria-json-tree__value is-boolean">{{ $valor ? 'true' : 'false' }}</span>
            @elseif(is_null($valor))
                <span class="auditoria-json-tree__value is-null">null</span>
            @elseif(is_int($valor) || is_float($valor))
                <span class="auditoria-json-tree__value is-number">{{ $valor }}</span>
            @else
                <span class="auditoria-json-tree__value is-string">{{ $valor === '' ? '""' : $valor }}</span>
            @endif
        </div>
    @endif
@endforeach
