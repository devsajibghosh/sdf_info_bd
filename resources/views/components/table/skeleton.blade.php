@props([
    'columnCount' => 6,
    'rowCount' => 10
])

<div id="custom-skeleton-loader">
    <table class="table">
        <thead>
            <tr>
                <th colspan="6">
                    <div class="skeleton-line w-100 h-4 my-1"></div>
                </th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $rowCount; $i++)
                <tr>
                    @for ($j = 0; $j < $columnCount; $j++)
                        <td><div style="height: 40px;" class="skeleton-line w-full h-4 my-1"></div></td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>
