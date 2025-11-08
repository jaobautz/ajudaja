<footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>AjudaJá</h5>
                    <p>Conectando quem precisa com quem pode ajudar. Uma plataforma comunitária para fortalecer laços e promover o bem.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Links Úteis</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/pages/index.php">Página Inicial</a></li>
                        <li><a href="#">Sobre Nós (Criar)</a></li>
                        <li><a href="#">Como Funciona (Criar)</a></li>
                        <li><a href="#">Perguntas Frequentes (Criar)</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="#">Termos de Uso (Criar)</a></li>
                        <li><a href="#">Política de Privacidade (Criar)</a></li>
                        <li><a href="#">Contato (Criar)</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> AjudaJá. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      // Garante que lucide exista antes de chamar
      if (typeof lucide !== 'undefined') {
          lucide.createIcons();
      }
    </script>
    
    <?php if (isset($include_chartjs) && $include_chartjs): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js" integrity="sha512-QexpKGW7L9MLD0N/o8dMh210gQfD/sOO0jDLRv051e/m7S/Mm8f8dJ5T9/A+13Vn8sB/wL5x/4vN+0i19MPSA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo BASE_URL; ?>/js/script.js" defer></script> 
</body>
</html>