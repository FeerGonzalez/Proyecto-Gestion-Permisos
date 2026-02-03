import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
  AbstractControl,
  ValidationErrors
} from '@angular/forms';
import { PermisoService } from '../../../core/services/permiso.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-crear',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './crear.component.html',
  styleUrl: './crear.component.css'
})
export class CrearComponent implements OnInit {

  form!: FormGroup;
  horasDisponibles = 0;
  error = '';
  minFecha!: string;

  constructor(
    private fb: FormBuilder,
    private permisoService: PermisoService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.minFecha = new Date().toISOString().split('T')[0];

    this.form = this.fb.group({
      fecha: ['', [Validators.required, this.fechaNoPasada]],
      hora_inicio: ['', Validators.required],
      hora_fin: ['', Validators.required],
      motivo: ['', [Validators.required, Validators.minLength(5)]],
    }, {
      validators: this.validarHorarioLaboral
    });
    
    this.form.get('fecha')?.setValue(this.minFecha);
    this.form.get('fecha')?.updateValueAndValidity();

    this.cargarHorasDisponibles();
  }

  cargarHorasDisponibles() {
    this.permisoService.horasDisponibles().subscribe({
      next: res => this.horasDisponibles = res.horas_disponibles
    });
  }

  /** Fecha >= hoy */
  fechaNoPasada(control: AbstractControl): ValidationErrors | null {
    if (!control.value) return null;

    const hoy = new Date().toISOString().split('T')[0];
    return control.value < hoy ? { fechaPasada: true } : null;
  }

  /** Horario laboral 07:30 a 13:30 */
  validarHorarioLaboral(control: AbstractControl): ValidationErrors | null {
    const fecha = control.get('fecha')?.value;
    const inicio = control.get('hora_inicio')?.value;
    const fin = control.get('hora_fin')?.value;

    if (!fecha || !inicio || !fin) return null;

    // Rango laboral
    if (inicio < '07:30' || fin > '13:30' || fin <= inicio) {
      return { horarioInvalido: true };
    }

    const hoy = new Date();
    const fechaSeleccionada = new Date(fecha);
    fechaSeleccionada.setHours(0, 0, 0, 0);

    const hoyNormalizado = new Date();
    hoyNormalizado.setHours(0, 0, 0, 0);

    if (fechaSeleccionada.getTime() === hoyNormalizado.getTime()) {
      const horaActual = hoy.toTimeString().slice(0, 5);

      if (inicio <= horaActual) {
        return { horaPasadaHoy: true };
      }
    }

    return null;
  }

  submit() {
    this.error = '';

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.permisoService.crear(this.form.value).subscribe({
      next: () => this.router.navigate(['/permisos']),
      error: err => {
        this.error = err.error?.message || 'Error al crear el permiso';
      }
    });
  }

  volver() {
    this.router.navigate(['/permisos']);
  }
}
