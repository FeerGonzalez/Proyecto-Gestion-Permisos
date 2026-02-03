import { User } from "./user.model";

export interface Permiso {
  id: number;
  fecha: string;
  hora_inicio: string;
  hora_fin: string;
  horas_totales: number;
  motivo: string;

  estado: 'pendiente' | 'aprobado' | 'rechazado' | 'cancelado';

  user_id: number;
  usuario?: User;

  examinado_por?: number | null;
  examinado_en?: string | null;
}