                            <tr wire:key="item-rural-1" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">1</td>
                                <td class="px-3.5 py-3 min-w-48">1. Enquadramento e Tensão de Fornecimento</td>
                                <td class="px-3.5 py-3 min-w-64">Carga instalada da unidade consumidora ≤ 75 kW → fornecimento previsto em baixa tensão (rede aérea secundária)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001 / NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-1" value="Sim" data-numero="1" data-tipo="conformidade" @change="setConformidade(1, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-1" value="Não" data-numero="1" data-tipo="conformidade" @change="setConformidade(1, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-1" value="N.A." checked data-numero="1" data-tipo="conformidade" @change="setConformidade(1, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="1" data-tipo="observacao" @input.debounce.300ms="setObservacao(1, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-2" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">2</td>
                                <td class="px-3.5 py-3 min-w-48">1. Enquadramento e Tensão de Fornecimento</td>
                                <td class="px-3.5 py-3 min-w-64">Carga instalada &gt; 75 kW → fornecimento previsto em média tensão (13,8 / 23,1 / 34,5 kV)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-2" value="Sim" data-numero="2" data-tipo="conformidade" @change="setConformidade(2, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-2" value="Não" data-numero="2" data-tipo="conformidade" @change="setConformidade(2, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-2" value="N.A." checked data-numero="2" data-tipo="conformidade" @change="setConformidade(2, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="2" data-tipo="observacao" @input.debounce.300ms="setObservacao(2, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-3" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">3</td>
                                <td class="px-3.5 py-3 min-w-48">1. Enquadramento e Tensão de Fornecimento</td>
                                <td class="px-3.5 py-3 min-w-64">Unidade consumidora com carga entre 50 kW e 75 kW: enquadramento no Grupo A devidamente justificado em estudo da concessionária?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-3" value="Sim" data-numero="3" data-tipo="conformidade" @change="setConformidade(3, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-3" value="Não" data-numero="3" data-tipo="conformidade" @change="setConformidade(3, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-3" value="N.A." checked data-numero="3" data-tipo="conformidade" @change="setConformidade(3, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="3" data-tipo="observacao" @input.debounce.300ms="setObservacao(3, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-4" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">4</td>
                                <td class="px-3.5 py-3 min-w-48">1. Enquadramento e Tensão de Fornecimento</td>
                                <td class="px-3.5 py-3 min-w-64">Condomínio/empreendimento de múltiplas unidades com carga &gt; 75 kW e ≤ 300 kVA: enquadrado no Grupo B com (i) mais de 50% das unidades em Grupo B, (ii) solicitação/concordância do consumidor e (iii) estudo de viabilidade da concessionária?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-4" value="Sim" data-numero="4" data-tipo="conformidade" @change="setConformidade(4, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-4" value="Não" data-numero="4" data-tipo="conformidade" @change="setConformidade(4, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-4" value="N.A." checked data-numero="4" data-tipo="conformidade" @change="setConformidade(4, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="4" data-tipo="observacao" @input.debounce.300ms="setObservacao(4, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-5" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">5</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal de conexão aéreo em BT com no máximo 30 m do ponto de ligação ao ponto de conexão (medição)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-5" value="Sim" data-numero="5" data-tipo="conformidade" @change="setConformidade(5, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-5" value="Não" data-numero="5" data-tipo="conformidade" @change="setConformidade(5, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-5" value="N.A." checked data-numero="5" data-tipo="conformidade" @change="setConformidade(5, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="5" data-tipo="observacao" @input.debounce.300ms="setObservacao(5, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-6" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">6</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal parte do poste da rede secundária mais próximo do ponto de conexão do consumidor?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-6" value="Sim" data-numero="6" data-tipo="conformidade" @change="setConformidade(6, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-6" value="Não" data-numero="6" data-tipo="conformidade" @change="setConformidade(6, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-6" value="N.A." checked data-numero="6" data-tipo="conformidade" @change="setConformidade(6, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="6" data-tipo="observacao" @input.debounce.300ms="setObservacao(6, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-7" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">7</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal entra pela frente do terreno/construção, no limite da via pública, sem atravessar terrenos de terceiros?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-7" value="Sim" data-numero="7" data-tipo="conformidade" @change="setConformidade(7, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-7" value="Não" data-numero="7" data-tipo="conformidade" @change="setConformidade(7, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-7" value="N.A." checked data-numero="7" data-tipo="conformidade" @change="setConformidade(7, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="7" data-tipo="observacao" @input.debounce.300ms="setObservacao(7, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-8" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">8</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal não cruza com condutores de ligação de prédios vizinhos?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-8" value="Sim" data-numero="8" data-tipo="conformidade" @change="setConformidade(8, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-8" value="Não" data-numero="8" data-tipo="conformidade" @change="setConformidade(8, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-8" value="N.A." checked data-numero="8" data-tipo="conformidade" @change="setConformidade(8, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="8" data-tipo="observacao" @input.debounce.300ms="setObservacao(8, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-9" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">9</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal mantém afastamento mínimo de 1,20 m de janelas, sacadas, telhados, escadas e áreas adjacentes?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 001</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-9" value="Sim" data-numero="9" data-tipo="conformidade" @change="setConformidade(9, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-9" value="Não" data-numero="9" data-tipo="conformidade" @change="setConformidade(9, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-9" value="N.A." checked data-numero="9" data-tipo="conformidade" @change="setConformidade(9, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="9" data-tipo="observacao" @input.debounce.300ms="setObservacao(9, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-10" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">10</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal derivado da rede de MT com bitola mínima de 35 mm² (13,8 kV) ou 50 mm² (23,1 kV)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-10" value="Sim" data-numero="10" data-tipo="conformidade" @change="setConformidade(10, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-10" value="Não" data-numero="10" data-tipo="conformidade" @change="setConformidade(10, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-10" value="N.A." checked data-numero="10" data-tipo="conformidade" @change="setConformidade(10, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="10" data-tipo="observacao" @input.debounce.300ms="setObservacao(10, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-11" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">11</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Vão livre do ramal de conexão em MT não excede 40 m (condições normais)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 004</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-11" value="Sim" data-numero="11" data-tipo="conformidade" @change="setConformidade(11, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-11" value="Não" data-numero="11" data-tipo="conformidade" @change="setConformidade(11, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-11" value="N.A." checked data-numero="11" data-tipo="conformidade" @change="setConformidade(11, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="11" data-tipo="observacao" @input.debounce.300ms="setObservacao(11, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-12" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">12</td>
                                <td class="px-3.5 py-3 min-w-48">2. Ramais de Conexão</td>
                                <td class="px-3.5 py-3 min-w-64">Postes locados de forma que o ramal de ligação ao consumidor não exceda 30 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-12" value="Sim" data-numero="12" data-tipo="conformidade" @change="setConformidade(12, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-12" value="Não" data-numero="12" data-tipo="conformidade" @change="setConformidade(12, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-12" value="N.A." checked data-numero="12" data-tipo="conformidade" @change="setConformidade(12, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="12" data-tipo="observacao" @input.debounce.300ms="setObservacao(12, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-13" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">13</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Redes rurais monofásicas (MRT) ou bifásicas: trecho de conexão com a concessionária em rede nua, demais trechos trifásicos em RDC?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-13" value="Sim" data-numero="13" data-tipo="conformidade" @change="setConformidade(13, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-13" value="Não" data-numero="13" data-tipo="conformidade" @change="setConformidade(13, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-13" value="N.A." checked data-numero="13" data-tipo="conformidade" @change="setConformidade(13, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="13" data-tipo="observacao" @input.debounce.300ms="setObservacao(13, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-14" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">14</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Vão rural (topografia plana): secundário ≤ 45 m e primário ≤ 80 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-14" value="Sim" data-numero="14" data-tipo="conformidade" @change="setConformidade(14, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-14" value="Não" data-numero="14" data-tipo="conformidade" @change="setConformidade(14, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-14" value="N.A." checked data-numero="14" data-tipo="conformidade" @change="setConformidade(14, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="14" data-tipo="observacao" @input.debounce.300ms="setObservacao(14, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-15" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">15</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Rede trifásica rural (vento 90 km/h): vão máximo de 110 m (cabo 1/0 AWG) ou 80 m (cabo 4/0 AWG / 336,4 MCM)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-15" value="Sim" data-numero="15" data-tipo="conformidade" @change="setConformidade(15, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-15" value="Não" data-numero="15" data-tipo="conformidade" @change="setConformidade(15, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-15" value="N.A." checked data-numero="15" data-tipo="conformidade" @change="setConformidade(15, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="15" data-tipo="observacao" @input.debounce.300ms="setObservacao(15, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-16" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">16</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Estruturas tipo pilar no poste (P1, P3, P4) aplicadas somente em redes rurais?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-16" value="Sim" data-numero="16" data-tipo="conformidade" @change="setConformidade(16, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-16" value="Não" data-numero="16" data-tipo="conformidade" @change="setConformidade(16, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-16" value="N.A." checked data-numero="16" data-tipo="conformidade" @change="setConformidade(16, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="16" data-tipo="observacao" @input.debounce.300ms="setObservacao(16, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-17" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">17</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Estruturas N1, N2, N3 e N4 utilizadas tanto em redes rurais como urbanas (fins de manutenção)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-17" value="Sim" data-numero="17" data-tipo="conformidade" @change="setConformidade(17, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-17" value="Não" data-numero="17" data-tipo="conformidade" @change="setConformidade(17, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-17" value="N.A." checked data-numero="17" data-tipo="conformidade" @change="setConformidade(17, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="17" data-tipo="observacao" @input.debounce.300ms="setObservacao(17, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-18" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">18</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de fibra aplicados apenas em áreas de difícil acesso/alta corrosividade ou com justificativa técnico-econômica, respeitando limite de TR até 300 kVA?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-18" value="Sim" data-numero="18" data-tipo="conformidade" @change="setConformidade(18, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-18" value="Não" data-numero="18" data-tipo="conformidade" @change="setConformidade(18, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-18" value="N.A." checked data-numero="18" data-tipo="conformidade" @change="setConformidade(18, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="18" data-tipo="observacao" @input.debounce.300ms="setObservacao(18, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-19" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">19</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Poste de concreto duplo T instalado com o lado de maior esforço posicionado para o encabeçamento da rede?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-19" value="Sim" data-numero="19" data-tipo="conformidade" @change="setConformidade(19, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-19" value="Não" data-numero="19" data-tipo="conformidade" @change="setConformidade(19, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-19" value="N.A." checked data-numero="19" data-tipo="conformidade" @change="setConformidade(19, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="19" data-tipo="observacao" @input.debounce.300ms="setObservacao(19, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-20" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">20</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Vãos contínuos sucessivos preveem ancoragem dupla (estruturas U4, UP4, P4, N4, T4, TE)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-20" value="Sim" data-numero="20" data-tipo="conformidade" @change="setConformidade(20, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-20" value="Não" data-numero="20" data-tipo="conformidade" @change="setConformidade(20, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-20" value="N.A." checked data-numero="20" data-tipo="conformidade" @change="setConformidade(20, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="20" data-tipo="observacao" @input.debounce.300ms="setObservacao(20, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-21" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">21</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de 9 m usados exclusivamente em BT, sem previsão de rede de MT?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-21" value="Sim" data-numero="21" data-tipo="conformidade" @change="setConformidade(21, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-21" value="Não" data-numero="21" data-tipo="conformidade" @change="setConformidade(21, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-21" value="N.A." checked data-numero="21" data-tipo="conformidade" @change="setConformidade(21, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="21" data-tipo="observacao" @input.debounce.300ms="setObservacao(21, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-22" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">22</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de 10 m usados em MT rural monofásica convencional (cabo nu) ou BT em condições especiais?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-22" value="Sim" data-numero="22" data-tipo="conformidade" @change="setConformidade(22, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-22" value="Não" data-numero="22" data-tipo="conformidade" @change="setConformidade(22, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-22" value="N.A." checked data-numero="22" data-tipo="conformidade" @change="setConformidade(22, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="22" data-tipo="observacao" @input.debounce.300ms="setObservacao(22, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-23" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">23</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de 11 m usados em MT bi/trifásica (cruzeta, pilar no poste ou compacta), com ou sem BT?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-23" value="Sim" data-numero="23" data-tipo="conformidade" @change="setConformidade(23, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-23" value="Não" data-numero="23" data-tipo="conformidade" @change="setConformidade(23, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-23" value="N.A." checked data-numero="23" data-tipo="conformidade" @change="setConformidade(23, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="23" data-tipo="observacao" @input.debounce.300ms="setObservacao(23, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-24" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">24</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de 12 m atendem mesmas condições dos de 11 m, com possibilidade de circuitos duplos?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-24" value="Sim" data-numero="24" data-tipo="conformidade" @change="setConformidade(24, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-24" value="Não" data-numero="24" data-tipo="conformidade" @change="setConformidade(24, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-24" value="N.A." checked data-numero="24" data-tipo="conformidade" @change="setConformidade(24, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="24" data-tipo="observacao" @input.debounce.300ms="setObservacao(24, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-25" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">25</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">RDR (condutores 4/0 AWG, 336,4 MCM ou liga 246,9/394,5 MCM): encabeçamento apenas HT ou HTE, poste mínimo 600 daN?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-25" value="Sim" data-numero="25" data-tipo="conformidade" @change="setConformidade(25, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-25" value="Não" data-numero="25" data-tipo="conformidade" @change="setConformidade(25, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-25" value="N.A." checked data-numero="25" data-tipo="conformidade" @change="setConformidade(25, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="25" data-tipo="observacao" @input.debounce.300ms="setObservacao(25, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-26" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">26</td>
                                <td class="px-3.5 py-3 min-w-48">3. Critérios Gerais de Rede — Vãos e Postes</td>
                                <td class="px-3.5 py-3 min-w-64">Estruturas N3, T3, N4, T4 com cruzeta tipo T de 250 daN em RDR: limitadas a condutores ≤ 1/0 AWG, vão rural máximo 80 m (topografia plana)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-26" value="Sim" data-numero="26" data-tipo="conformidade" @change="setConformidade(26, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-26" value="Não" data-numero="26" data-tipo="conformidade" @change="setConformidade(26, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-26" value="N.A." checked data-numero="26" data-tipo="conformidade" @change="setConformidade(26, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="26" data-tipo="observacao" @input.debounce.300ms="setObservacao(26, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-27" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">27</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">RDC projetada em trechos rurais com alto índice de arborização ou em circuitos duplos?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-27" value="Sim" data-numero="27" data-tipo="conformidade" @change="setConformidade(27, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-27" value="Não" data-numero="27" data-tipo="conformidade" @change="setConformidade(27, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-27" value="N.A." checked data-numero="27" data-tipo="conformidade" @change="setConformidade(27, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="27" data-tipo="observacao" @input.debounce.300ms="setObservacao(27, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-28" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">28</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">RDC não projetada sobre terrenos de terceiros?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-28" value="Sim" data-numero="28" data-tipo="conformidade" @change="setConformidade(28, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-28" value="Não" data-numero="28" data-tipo="conformidade" @change="setConformidade(28, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-28" value="N.A." checked data-numero="28" data-tipo="conformidade" @change="setConformidade(28, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="28" data-tipo="observacao" @input.debounce.300ms="setObservacao(28, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-29" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">29</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Derivações preferencialmente perpendiculares à rede, com 1º poste a no máximo 40 m da derivação?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-29" value="Sim" data-numero="29" data-tipo="conformidade" @change="setConformidade(29, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-29" value="Não" data-numero="29" data-tipo="conformidade" @change="setConformidade(29, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-29" value="N.A." checked data-numero="29" data-tipo="conformidade" @change="setConformidade(29, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="29" data-tipo="observacao" @input.debounce.300ms="setObservacao(29, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-30" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">30</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Cabo coberto XLPE+HDPE de alumínio e cabo mensageiro em cordoalha de aço zincado corretamente especificados?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-30" value="Sim" data-numero="30" data-tipo="conformidade" @change="setConformidade(30, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-30" value="Não" data-numero="30" data-tipo="conformidade" @change="setConformidade(30, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-30" value="N.A." checked data-numero="30" data-tipo="conformidade" @change="setConformidade(30, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="30" data-tipo="observacao" @input.debounce.300ms="setObservacao(30, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-31" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">31</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Seção do tronco de alimentadores ≥ 150 mm²/185 mm²; derivações em 50 mm²/70 mm² conforme carga?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018 / NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-31" value="Sim" data-numero="31" data-tipo="conformidade" @change="setConformidade(31, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-31" value="Não" data-numero="31" data-tipo="conformidade" @change="setConformidade(31, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-31" value="N.A." checked data-numero="31" data-tipo="conformidade" @change="setConformidade(31, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="31" data-tipo="observacao" @input.debounce.300ms="setObservacao(31, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-32" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">32</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Postes RDC com altura mínima de 11 m (13,8 kV, estruturas simples) ou 12 m (equipamentos, derivação, tronco, ou 23,1/34,5 kV)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-32" value="Sim" data-numero="32" data-tipo="conformidade" @change="setConformidade(32, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-32" value="Não" data-numero="32" data-tipo="conformidade" @change="setConformidade(32, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-32" value="N.A." checked data-numero="32" data-tipo="conformidade" @change="setConformidade(32, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="32" data-tipo="observacao" @input.debounce.300ms="setObservacao(32, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-33" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">33</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Ângulos de deflexão da RDC evitados; estrutura CE1-A prevista a cada 3 estruturas CE1 em sequência?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-33" value="Sim" data-numero="33" data-tipo="conformidade" @change="setConformidade(33, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-33" value="Não" data-numero="33" data-tipo="conformidade" @change="setConformidade(33, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-33" value="N.A." checked data-numero="33" data-tipo="conformidade" @change="setConformidade(33, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="33" data-tipo="observacao" @input.debounce.300ms="setObservacao(33, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-34" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">34</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Estruturas de ancoragem projetadas em intervalos máximos de 400 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-34" value="Sim" data-numero="34" data-tipo="conformidade" @change="setConformidade(34, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-34" value="Não" data-numero="34" data-tipo="conformidade" @change="setConformidade(34, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-34" value="N.A." checked data-numero="34" data-tipo="conformidade" @change="setConformidade(34, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="34" data-tipo="observacao" @input.debounce.300ms="setObservacao(34, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-35" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">35</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Aterramento do cabo mensageiro previsto em pontos críticos (para-raios/equipamentos/fim de rede) e a cada 300 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-35" value="Sim" data-numero="35" data-tipo="conformidade" @change="setConformidade(35, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-35" value="Não" data-numero="35" data-tipo="conformidade" @change="setConformidade(35, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-35" value="N.A." checked data-numero="35" data-tipo="conformidade" @change="setConformidade(35, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="35" data-tipo="observacao" @input.debounce.300ms="setObservacao(35, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-36" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">36</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Vão máximo de 60 m onde houver só MT e 40 m onde houver BT; estrutura CE2 usada em vãos de 60 m com cabo 185 mm²?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-36" value="Sim" data-numero="36" data-tipo="conformidade" @change="setConformidade(36, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-36" value="Não" data-numero="36" data-tipo="conformidade" @change="setConformidade(36, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-36" value="N.A." checked data-numero="36" data-tipo="conformidade" @change="setConformidade(36, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="36" data-tipo="observacao" @input.debounce.300ms="setObservacao(36, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-37" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">37</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Condutores protegidos em RDC rural aplicados apenas em áreas de grande arborização, com para-raios na transição alumínio nu → protegido?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-37" value="Sim" data-numero="37" data-tipo="conformidade" @change="setConformidade(37, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-37" value="Não" data-numero="37" data-tipo="conformidade" @change="setConformidade(37, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-37" value="N.A." checked data-numero="37" data-tipo="conformidade" @change="setConformidade(37, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="37" data-tipo="observacao" @input.debounce.300ms="setObservacao(37, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-38" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">38</td>
                                <td class="px-3.5 py-3 min-w-48">4. Rede de Distribuição Compacta (RDC)</td>
                                <td class="px-3.5 py-3 min-w-64">Ventos acima de 90 km/h e cabo monofásico: aplicação possível exclusivamente em zona rural?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-38" value="Sim" data-numero="38" data-tipo="conformidade" @change="setConformidade(38, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-38" value="Não" data-numero="38" data-tipo="conformidade" @change="setConformidade(38, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-38" value="N.A." checked data-numero="38" data-tipo="conformidade" @change="setConformidade(38, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="38" data-tipo="observacao" @input.debounce.300ms="setObservacao(38, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-39" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">39</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR exclusivo para atendimento de unidade única do Grupo B em área rural: permitido?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-39" value="Sim" data-numero="39" data-tipo="conformidade" @change="setConformidade(39, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-39" value="Não" data-numero="39" data-tipo="conformidade" @change="setConformidade(39, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-39" value="N.A." checked data-numero="39" data-tipo="conformidade" @change="setConformidade(39, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="39" data-tipo="observacao" @input.debounce.300ms="setObservacao(39, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-40" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">40</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR em área rural: poste mínimo de 11 m (exceto reaproveitamento de poste 10 m existente)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-40" value="Sim" data-numero="40" data-tipo="conformidade" @change="setConformidade(40, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-40" value="Não" data-numero="40" data-tipo="conformidade" @change="setConformidade(40, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-40" value="N.A." checked data-numero="40" data-tipo="conformidade" @change="setConformidade(40, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="40" data-tipo="observacao" @input.debounce.300ms="setObservacao(40, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-41" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">41</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR monofásico em área rural (sistema MRT): ponto de medição do cliente entre 15 m e 23 m do poste do TR?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-41" value="Sim" data-numero="41" data-tipo="conformidade" @change="setConformidade(41, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-41" value="Não" data-numero="41" data-tipo="conformidade" @change="setConformidade(41, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-41" value="N.A." checked data-numero="41" data-tipo="conformidade" @change="setConformidade(41, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="41" data-tipo="observacao" @input.debounce.300ms="setObservacao(41, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-42" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">42</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR locado, tanto quanto possível, no centro de carga da área de atendimento?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-42" value="Sim" data-numero="42" data-tipo="conformidade" @change="setConformidade(42, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-42" value="Não" data-numero="42" data-tipo="conformidade" @change="setConformidade(42, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-42" value="N.A." checked data-numero="42" data-tipo="conformidade" @change="setConformidade(42, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="42" data-tipo="observacao" @input.debounce.300ms="setObservacao(42, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-43" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">43</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR não instalado em esquinas, postes de ângulo, derivação ou cruzamento?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005 / NT 006 / NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-43" value="Sim" data-numero="43" data-tipo="conformidade" @change="setConformidade(43, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-43" value="Não" data-numero="43" data-tipo="conformidade" @change="setConformidade(43, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-43" value="N.A." checked data-numero="43" data-tipo="conformidade" @change="setConformidade(43, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="43" data-tipo="observacao" @input.debounce.300ms="setObservacao(43, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-44" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">44</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">TR não instalado em frente a edificações com marquises/sacadas sem afastamento mínimo?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-44" value="Sim" data-numero="44" data-tipo="conformidade" @change="setConformidade(44, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-44" value="Não" data-numero="44" data-tipo="conformidade" @change="setConformidade(44, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-44" value="N.A." checked data-numero="44" data-tipo="conformidade" @change="setConformidade(44, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="44" data-tipo="observacao" @input.debounce.300ms="setObservacao(44, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-45" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">45</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Comprimento do circuito de BT a partir do TR não excede 400 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-45" value="Sim" data-numero="45" data-tipo="conformidade" @change="setConformidade(45, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-45" value="Não" data-numero="45" data-tipo="conformidade" @change="setConformidade(45, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-45" value="N.A." checked data-numero="45" data-tipo="conformidade" @change="setConformidade(45, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="45" data-tipo="observacao" @input.debounce.300ms="setObservacao(45, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-46" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">46</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Carregamento do TR ≤ 112,5 kVA — havendo atingimento do máximo, estudo de subdivisão realizado?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005 / NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-46" value="Sim" data-numero="46" data-tipo="conformidade" @change="setConformidade(46, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-46" value="Não" data-numero="46" data-tipo="conformidade" @change="setConformidade(46, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-46" value="N.A." checked data-numero="46" data-tipo="conformidade" @change="setConformidade(46, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="46" data-tipo="observacao" @input.debounce.300ms="setObservacao(46, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-47" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">47</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Em rede trifásica, apenas TR bifásico ou trifásico instalado?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-47" value="Sim" data-numero="47" data-tipo="conformidade" @change="setConformidade(47, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-47" value="Não" data-numero="47" data-tipo="conformidade" @change="setConformidade(47, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-47" value="N.A." checked data-numero="47" data-tipo="conformidade" @change="setConformidade(47, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="47" data-tipo="observacao" @input.debounce.300ms="setObservacao(47, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-48" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">48</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Ramal exclusivo de até 150 m com apenas um TR: chave fusível suprimida somente se visível e de livre acesso?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-48" value="Sim" data-numero="48" data-tipo="conformidade" @change="setConformidade(48, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-48" value="Não" data-numero="48" data-tipo="conformidade" @change="setConformidade(48, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-48" value="N.A." checked data-numero="48" data-tipo="conformidade" @change="setConformidade(48, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="48" data-tipo="observacao" @input.debounce.300ms="setObservacao(48, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-49" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">49</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Não é permitida derivação de poste de transformador?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-49" value="Sim" data-numero="49" data-tipo="conformidade" @change="setConformidade(49, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-49" value="Não" data-numero="49" data-tipo="conformidade" @change="setConformidade(49, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-49" value="N.A." checked data-numero="49" data-tipo="conformidade" @change="setConformidade(49, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="49" data-tipo="observacao" @input.debounce.300ms="setObservacao(49, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-50" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">50</td>
                                <td class="px-3.5 py-3 min-w-48">5. Transformadores</td>
                                <td class="px-3.5 py-3 min-w-64">Circuitos duplos instalados em dois níveis?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-50" value="Sim" data-numero="50" data-tipo="conformidade" @change="setConformidade(50, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-50" value="Não" data-numero="50" data-tipo="conformidade" @change="setConformidade(50, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-50" value="N.A." checked data-numero="50" data-tipo="conformidade" @change="setConformidade(50, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="50" data-tipo="observacao" @input.debounce.300ms="setObservacao(50, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-51" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">51</td>
                                <td class="px-3.5 py-3 min-w-48">6. Estruturas para Equipamentos e Religadores</td>
                                <td class="px-3.5 py-3 min-w-64">Religador instalado entre duas estruturas CE4 (rede compacta) ou N4 (rede convencional)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 007</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-51" value="Sim" data-numero="51" data-tipo="conformidade" @change="setConformidade(51, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-51" value="Não" data-numero="51" data-tipo="conformidade" @change="setConformidade(51, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-51" value="N.A." checked data-numero="51" data-tipo="conformidade" @change="setConformidade(51, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="51" data-tipo="observacao" @input.debounce.300ms="setObservacao(51, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-52" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">52</td>
                                <td class="px-3.5 py-3 min-w-48">6. Estruturas para Equipamentos e Religadores</td>
                                <td class="px-3.5 py-3 min-w-64">Poste de instalação do religador com mínimo 600 daN e 12 m, com ancoragem (e postes adjacentes também)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 007 / NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-52" value="Sim" data-numero="52" data-tipo="conformidade" @change="setConformidade(52, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-52" value="Não" data-numero="52" data-tipo="conformidade" @change="setConformidade(52, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-52" value="N.A." checked data-numero="52" data-tipo="conformidade" @change="setConformidade(52, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="52" data-tipo="observacao" @input.debounce.300ms="setObservacao(52, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-53" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">53</td>
                                <td class="px-3.5 py-3 min-w-48">6. Estruturas para Equipamentos e Religadores</td>
                                <td class="px-3.5 py-3 min-w-64">Religador monofásico NÃO instalado em rede trifásica?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 007</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-53" value="Sim" data-numero="53" data-tipo="conformidade" @change="setConformidade(53, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-53" value="Não" data-numero="53" data-tipo="conformidade" @change="setConformidade(53, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-53" value="N.A." checked data-numero="53" data-tipo="conformidade" @change="setConformidade(53, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="53" data-tipo="observacao" @input.debounce.300ms="setObservacao(53, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-54" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">54</td>
                                <td class="px-3.5 py-3 min-w-48">6. Estruturas para Equipamentos e Religadores</td>
                                <td class="px-3.5 py-3 min-w-64">Em circuito duplo, equipamentos preferencialmente conectados ao circuito inferior?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-54" value="Sim" data-numero="54" data-tipo="conformidade" @change="setConformidade(54, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-54" value="Não" data-numero="54" data-tipo="conformidade" @change="setConformidade(54, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-54" value="N.A." checked data-numero="54" data-tipo="conformidade" @change="setConformidade(54, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="54" data-tipo="observacao" @input.debounce.300ms="setObservacao(54, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-55" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">55</td>
                                <td class="px-3.5 py-3 min-w-48">7. Chaves de Manobra e Proteção</td>
                                <td class="px-3.5 py-3 min-w-64">Chaves seccionadoras faca ou fusível instaladas a cada 5 km em linhas rurais?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005 / NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-55" value="Sim" data-numero="55" data-tipo="conformidade" @change="setConformidade(55, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-55" value="Não" data-numero="55" data-tipo="conformidade" @change="setConformidade(55, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-55" value="N.A." checked data-numero="55" data-tipo="conformidade" @change="setConformidade(55, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="55" data-tipo="observacao" @input.debounce.300ms="setObservacao(55, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-56" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">56</td>
                                <td class="px-3.5 py-3 min-w-48">7. Chaves de Manobra e Proteção</td>
                                <td class="px-3.5 py-3 min-w-64">Uso de chaves fusíveis limitado a no máximo 3 em série (coordenação/seletividade)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-56" value="Sim" data-numero="56" data-tipo="conformidade" @change="setConformidade(56, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-56" value="Não" data-numero="56" data-tipo="conformidade" @change="setConformidade(56, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-56" value="N.A." checked data-numero="56" data-tipo="conformidade" @change="setConformidade(56, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="56" data-tipo="observacao" @input.debounce.300ms="setObservacao(56, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-57" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">57</td>
                                <td class="px-3.5 py-3 min-w-48">7. Chaves de Manobra e Proteção</td>
                                <td class="px-3.5 py-3 min-w-64">Chaves sem carga instaladas a intervalos máximos de 3 km ao longo do tronco do alimentador (RDC)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-57" value="Sim" data-numero="57" data-tipo="conformidade" @change="setConformidade(57, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-57" value="Não" data-numero="57" data-tipo="conformidade" @change="setConformidade(57, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-57" value="N.A." checked data-numero="57" data-tipo="conformidade" @change="setConformidade(57, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="57" data-tipo="observacao" @input.debounce.300ms="setObservacao(57, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-58" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">58</td>
                                <td class="px-3.5 py-3 min-w-48">7. Chaves de Manobra e Proteção</td>
                                <td class="px-3.5 py-3 min-w-64">Chaves com carga instaladas nos pontos de interligação de alimentadores?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 0018</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-58" value="Sim" data-numero="58" data-tipo="conformidade" @change="setConformidade(58, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-58" value="Não" data-numero="58" data-tipo="conformidade" @change="setConformidade(58, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-58" value="N.A." checked data-numero="58" data-tipo="conformidade" @change="setConformidade(58, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="58" data-tipo="observacao" @input.debounce.300ms="setObservacao(58, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-59" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">59</td>
                                <td class="px-3.5 py-3 min-w-48">8. Condutores</td>
                                <td class="px-3.5 py-3 min-w-64">Trecho tronco em MT: alumínio 336 MCM/4/0 AWG (nu) ou protegido 185/150 mm² XLPE+HDPE, conforme carga?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-59" value="Sim" data-numero="59" data-tipo="conformidade" @change="setConformidade(59, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-59" value="Não" data-numero="59" data-tipo="conformidade" @change="setConformidade(59, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-59" value="N.A." checked data-numero="59" data-tipo="conformidade" @change="setConformidade(59, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="59" data-tipo="observacao" @input.debounce.300ms="setObservacao(59, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-60" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">60</td>
                                <td class="px-3.5 py-3 min-w-48">8. Condutores</td>
                                <td class="px-3.5 py-3 min-w-64">Trecho de ramal em MT: alumínio nu mínimo 1/0 AWG (ou CAL em zona de alta corrosividade)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-60" value="Sim" data-numero="60" data-tipo="conformidade" @change="setConformidade(60, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-60" value="Não" data-numero="60" data-tipo="conformidade" @change="setConformidade(60, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-60" value="N.A." checked data-numero="60" data-tipo="conformidade" @change="setConformidade(60, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="60" data-tipo="observacao" @input.debounce.300ms="setObservacao(60, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-61" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">61</td>
                                <td class="px-3.5 py-3 min-w-48">8. Condutores</td>
                                <td class="px-3.5 py-3 min-w-64">Condutores de BT rural: 1x35+1x35 mm², fase CA, isolação XLPE preta, neutro nu CAL?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-61" value="Sim" data-numero="61" data-tipo="conformidade" @change="setConformidade(61, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-61" value="Não" data-numero="61" data-tipo="conformidade" @change="setConformidade(61, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-61" value="N.A." checked data-numero="61" data-tipo="conformidade" @change="setConformidade(61, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="61" data-tipo="observacao" @input.debounce.300ms="setObservacao(61, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-62" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">62</td>
                                <td class="px-3.5 py-3 min-w-48">8. Condutores</td>
                                <td class="px-3.5 py-3 min-w-64">Cabos de alumínio com alma (CAA) recomendados para MT rural em zonas de corrosão C2/C3?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-62" value="Sim" data-numero="62" data-tipo="conformidade" @change="setConformidade(62, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-62" value="Não" data-numero="62" data-tipo="conformidade" @change="setConformidade(62, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-62" value="N.A." checked data-numero="62" data-tipo="conformidade" @change="setConformidade(62, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="62" data-tipo="observacao" @input.debounce.300ms="setObservacao(62, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-63" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">63</td>
                                <td class="px-3.5 py-3 min-w-48">9. Padronização de Materiais por Zona de Corrosão</td>
                                <td class="px-3.5 py-3 min-w-64">Zona C2 (&gt; 10 km da orla) e C3 (5–10 km): cabo CA/CAA/RDC e poste de concreto duplo T?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006 / NT 008</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-63" value="Sim" data-numero="63" data-tipo="conformidade" @change="setConformidade(63, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-63" value="Não" data-numero="63" data-tipo="conformidade" @change="setConformidade(63, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-63" value="N.A." checked data-numero="63" data-tipo="conformidade" @change="setConformidade(63, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="63" data-tipo="observacao" @input.debounce.300ms="setObservacao(63, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-64" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">64</td>
                                <td class="px-3.5 py-3 min-w-48">9. Padronização de Materiais por Zona de Corrosão</td>
                                <td class="px-3.5 py-3 min-w-64">Zona C4 (2–5 km da orla): cabo CAL e poste de concreto duplo T?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006 / NT 008</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-64" value="Sim" data-numero="64" data-tipo="conformidade" @change="setConformidade(64, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-64" value="Não" data-numero="64" data-tipo="conformidade" @change="setConformidade(64, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-64" value="N.A." checked data-numero="64" data-tipo="conformidade" @change="setConformidade(64, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="64" data-tipo="observacao" @input.debounce.300ms="setObservacao(64, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-65" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">65</td>
                                <td class="px-3.5 py-3 min-w-48">9. Padronização de Materiais por Zona de Corrosão</td>
                                <td class="px-3.5 py-3 min-w-64">Zona C5 (&lt; 2 km da orla): cabo CAL e poste de fibra de vidro?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 008</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-65" value="Sim" data-numero="65" data-tipo="conformidade" @change="setConformidade(65, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-65" value="Não" data-numero="65" data-tipo="conformidade" @change="setConformidade(65, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-65" value="N.A." checked data-numero="65" data-tipo="conformidade" @change="setConformidade(65, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="65" data-tipo="observacao" @input.debounce.300ms="setObservacao(65, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-66" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">66</td>
                                <td class="px-3.5 py-3 min-w-48">9. Padronização de Materiais por Zona de Corrosão</td>
                                <td class="px-3.5 py-3 min-w-64">Cruzetas de fibra de vidro especificadas em todas as zonas de corrosão (C2 a C5)?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 008</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-66" value="Sim" data-numero="66" data-tipo="conformidade" @change="setConformidade(66, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-66" value="Não" data-numero="66" data-tipo="conformidade" @change="setConformidade(66, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-66" value="N.A." checked data-numero="66" data-tipo="conformidade" @change="setConformidade(66, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="66" data-tipo="observacao" @input.debounce.300ms="setObservacao(66, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-67" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">67</td>
                                <td class="px-3.5 py-3 min-w-48">9. Padronização de Materiais por Zona de Corrosão</td>
                                <td class="px-3.5 py-3 min-w-64">Postes de fibra utilizados em áreas de difícil acesso, quando aplicável?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005 / NT 008</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-67" value="Sim" data-numero="67" data-tipo="conformidade" @change="setConformidade(67, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-67" value="Não" data-numero="67" data-tipo="conformidade" @change="setConformidade(67, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-67" value="N.A." checked data-numero="67" data-tipo="conformidade" @change="setConformidade(67, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="67" data-tipo="observacao" @input.debounce.300ms="setObservacao(67, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-68" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">68</td>
                                <td class="px-3.5 py-3 min-w-48">10. Aterramentos, Cercas e Estais</td>
                                <td class="px-3.5 py-3 min-w-64">Estais previstos em postes de ancoragem, encabeçamento, ângulo ou derivação, com preferência por estai de âncora?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Geral</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-68" value="Sim" data-numero="68" data-tipo="conformidade" @change="setConformidade(68, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-68" value="Não" data-numero="68" data-tipo="conformidade" @change="setConformidade(68, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-68" value="N.A." checked data-numero="68" data-tipo="conformidade" @change="setConformidade(68, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="68" data-tipo="observacao" @input.debounce.300ms="setObservacao(68, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-69" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">69</td>
                                <td class="px-3.5 py-3 min-w-48">10. Aterramentos, Cercas e Estais</td>
                                <td class="px-3.5 py-3 min-w-64">Estais NÃO utilizados em áreas agricultáveis?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-69" value="Sim" data-numero="69" data-tipo="conformidade" @change="setConformidade(69, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-69" value="Não" data-numero="69" data-tipo="conformidade" @change="setConformidade(69, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-69" value="N.A." checked data-numero="69" data-tipo="conformidade" @change="setConformidade(69, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="69" data-tipo="observacao" @input.debounce.300ms="setObservacao(69, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-70" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">70</td>
                                <td class="px-3.5 py-3 min-w-48">10. Aterramentos, Cercas e Estais</td>
                                <td class="px-3.5 py-3 min-w-64">Cercas paralelas à rede rural (≤ 30 m): seccionadas e aterradas a cada 250 m, com seccionador pré-formado em cruzamentos transversais?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 005 / NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-70" value="Sim" data-numero="70" data-tipo="conformidade" @change="setConformidade(70, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-70" value="Não" data-numero="70" data-tipo="conformidade" @change="setConformidade(70, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-70" value="N.A." checked data-numero="70" data-tipo="conformidade" @change="setConformidade(70, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="70" data-tipo="observacao" @input.debounce.300ms="setObservacao(70, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-71" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">71</td>
                                <td class="px-3.5 py-3 min-w-48">10. Aterramentos, Cercas e Estais</td>
                                <td class="px-3.5 py-3 min-w-64">Ao redor de pontos de instalação de TR, cercas seccionadas em trecho de 20 m?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-71" value="Sim" data-numero="71" data-tipo="conformidade" @change="setConformidade(71, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-71" value="Não" data-numero="71" data-tipo="conformidade" @change="setConformidade(71, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-71" value="N.A." checked data-numero="71" data-tipo="conformidade" @change="setConformidade(71, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="71" data-tipo="observacao" @input.debounce.300ms="setObservacao(71, $event.target.value)" />
                                </td>
                            </tr>
                            <tr wire:key="item-rural-72" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">72</td>
                                <td class="px-3.5 py-3 min-w-48">10. Aterramentos, Cercas e Estais</td>
                                <td class="px-3.5 py-3 min-w-64">Cercas a mais de 30 m da rede rural corretamente dispensadas de aterramento?</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">NT 006</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">Rural</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-72" value="Sim" data-numero="72" data-tipo="conformidade" @change="setConformidade(72, 'Sim')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-72" value="Não" data-numero="72" data-tipo="conformidade" @change="setConformidade(72, 'Não')" />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input type="radio" class="form-radio size-4 text-success" name="conformidade-rural-72" value="N.A." checked data-numero="72" data-tipo="conformidade" @change="setConformidade(72, 'N.A.')" />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input type="text" class="form-input form-input-sm w-full min-w-48" placeholder="Observações" data-numero="72" data-tipo="observacao" @input.debounce.300ms="setObservacao(72, $event.target.value)" />
                                </td>
                            </tr>
