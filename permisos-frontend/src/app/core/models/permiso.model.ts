import { User } from "./user.model";

export interface Permiso {
  id: number;
  fecha: string;
  hora_inicio: string;
  hora_fin: string;
  horas_totales: number;
  motivo: string;
  estado: 'pendiente' | 'aprobado' | 'rechazado';
  aprobado_por?: number | null;
  user_id: number;
  usuario?: User;
}
