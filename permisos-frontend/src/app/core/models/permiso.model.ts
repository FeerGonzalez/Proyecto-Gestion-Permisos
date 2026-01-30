export type EstadoPermiso = 'pendiente' | 'aprobado' | 'rechazado' | 'cancelado';

export interface Permiso {
  id: number;
  fecha: string;
  hora_inicio: string;
  hora_fin: string;
  horas_totales: number;
  motivo: string;
  estado: EstadoPermiso;
  aprobado_por?: string;
  aprobado_en?: string;
}