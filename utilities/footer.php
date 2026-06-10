<!-- Modal de Confirmación Global -->
<div id="modal-confirmacion" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-7">

        <div class="flex items-center gap-4 mb-3">
            <div id="mc-icon" class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"></div>
            <h2 id="mc-titulo" class="text-xl font-bold text-gray-800 leading-tight"></h2>
        </div>

        <p id="mc-descripcion" class="text-sm text-gray-400 mb-5 leading-relaxed"></p>

        <div class="bg-gray-50 rounded-2xl p-4 flex items-center gap-4 mb-6">
            <div id="mc-preview-visual" class="w-14 h-14 rounded-xl flex-shrink-0 overflow-hidden flex items-center justify-center"></div>
            <div class="flex-1 min-w-0">
                <p id="mc-preview-nombre" class="font-bold text-gray-800 truncate text-sm"></p>
                <p id="mc-preview-detalle" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="cerrarModalConfirmacion()"
                    class="px-5 py-2.5 text-gray-400 font-semibold hover:text-gray-600 transition text-sm">
                Cancelar
            </button>
            <button type="button" id="mc-btn-confirmar" onclick="ejecutarConfirmacion()"
                    class="px-6 py-2.5 bg-[#BC5F40] text-white rounded-xl font-semibold hover:bg-[#a04e35] transition text-sm">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script src="/js/main.js"></script>
</body>
</html>