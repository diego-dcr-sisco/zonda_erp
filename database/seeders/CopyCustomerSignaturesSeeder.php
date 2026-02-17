<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CopyCustomerSignaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder copia las firmas de clientes entre órdenes del mismo customer_id
     * cuando tienen el mismo signature_name.
     * 
     * @return void
     */
    public function run()
    {
        // Definir el rango de fechas
        $startDate = '2024-01-01'; // Ajustar según necesidad
        $endDate = '2024-12-31';   // Ajustar según necesidad

        $this->command->info("Iniciando proceso de copia de firmas...");
        $this->command->info("Rango de fechas: {$startDate} a {$endDate}");

        // Obtener órdenes con status_id = 5 en el rango de fechas
        // que NO tengan firma (customer_signature vacío o null)
        $ordersWithoutSignature = Order::where('status_id', 5)
            ->whereBetween('programmed_date', [$startDate, $endDate])
            ->where(function($query) {
                $query->whereNull('customer_signature')
                      ->orWhere('customer_signature', '');
            })
            ->whereNotNull('signature_name')
            ->where('signature_name', '!=', '')
            ->whereNotNull('customer_id')
            ->get();

        $this->command->info("Órdenes encontradas sin firma: " . $ordersWithoutSignature->count());

        $processedCount = 0;
        $updatedCount = 0;
        $notFoundCount = 0;

        foreach ($ordersWithoutSignature as $order) {
            $processedCount++;
            
            $this->command->info("\n[{$processedCount}/{$ordersWithoutSignature->count()}] Procesando Orden ID: {$order->id}");
            $this->command->info("  - Customer ID: {$order->customer_id}");
            $this->command->info("  - Signature Name: {$order->signature_name}");

            // Buscar otra orden del mismo customer_id con el mismo signature_name
            // que SÍ tenga firma
            $orderWithSignature = Order::where('customer_id', $order->customer_id)
                ->where('signature_name', $order->signature_name)
                ->whereNotNull('customer_signature')
                ->where('customer_signature', '!=', '')
                ->where('id', '!=', $order->id) // Excluir la orden actual
                ->orderBy('updated_at', 'desc') // Obtener la más reciente
                ->first();

            if ($orderWithSignature) {
                try {
                    // Copiar la firma
                    $order->customer_signature = $orderWithSignature->customer_signature;
                    
                    // Copiar también customer_sig_path si existe
                    if (!empty($orderWithSignature->customer_sig_path)) {
                        $order->customer_sig_path = $orderWithSignature->customer_sig_path;
                    }
                    
                    $order->save();

                    $updatedCount++;
                    $this->command->info("  ✓ Firma copiada desde Orden ID: {$orderWithSignature->id}");
                    
                    Log::info("Firma copiada", [
                        'target_order_id' => $order->id,
                        'source_order_id' => $orderWithSignature->id,
                        'customer_id' => $order->customer_id,
                        'signature_name' => $order->signature_name
                    ]);
                    
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Error al copiar firma: " . $e->getMessage());
                    Log::error("Error al copiar firma", [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $notFoundCount++;
                $this->command->warn("  ⚠ No se encontró orden con firma para este signature_name");
                
                Log::warning("No se encontró firma de referencia", [
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'signature_name' => $order->signature_name
                ]);
            }
        }

        // Resumen
        $this->command->info("\n" . str_repeat("=", 60));
        $this->command->info("RESUMEN DEL PROCESO");
        $this->command->info(str_repeat("=", 60));
        $this->command->info("Total de órdenes procesadas: {$processedCount}");
        $this->command->info("Órdenes actualizadas con firma: {$updatedCount}");
        $this->command->info("Órdenes sin firma de referencia: {$notFoundCount}");
        $this->command->info(str_repeat("=", 60));

        // Verificación adicional: mostrar estadísticas generales
        $this->showStatistics($startDate, $endDate);
    }

    /**
     * Mostrar estadísticas generales de órdenes
     */
    private function showStatistics($startDate, $endDate)
    {
        $this->command->info("\nESTADÍSTICAS GENERALES:");
        
        $totalOrdersStatus5 = Order::where('status_id', 5)
            ->whereBetween('programmed_date', [$startDate, $endDate])
            ->count();
            
        $ordersWithSignature = Order::where('status_id', 5)
            ->whereBetween('programmed_date', [$startDate, $endDate])
            ->whereNotNull('customer_signature')
            ->where('customer_signature', '!=', '')
            ->count();
            
        $ordersWithoutSignature = Order::where('status_id', 5)
            ->whereBetween('programmed_date', [$startDate, $endDate])
            ->where(function($query) {
                $query->whereNull('customer_signature')
                      ->orWhere('customer_signature', '');
            })
            ->count();

        $this->command->info("Total órdenes con status_id = 5: {$totalOrdersStatus5}");
        $this->command->info("Órdenes CON firma: {$ordersWithSignature}");
        $this->command->info("Órdenes SIN firma: {$ordersWithoutSignature}");
        
        if ($totalOrdersStatus5 > 0) {
            $percentage = round(($ordersWithSignature / $totalOrdersStatus5) * 100, 2);
            $this->command->info("Porcentaje con firma: {$percentage}%");
        }
    }
}
