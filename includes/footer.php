  </main>
</div>

<!-- Add expense -->
<div class="modal" id="modal-expense" aria-hidden="true">
  <form class="modal-card" action="actions.php" method="post">
    <h3>Add expense</h3>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_expense">
    <input type="hidden" name="redirect" value="<?= e($self) ?>">
    <div class="form-grid">
      <label>Amount (₹)<input type="number" name="amount" step="0.01" min="0.01" required autofocus></label>
      <label>Date<input type="date" name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></label>
      <label>Category
        <select name="category_id" required>
          <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Sub category<input type="text" name="sub_category" maxlength="80" placeholder="e.g. Swiggy, Amazon"></label>
      <label>Payment mode
        <select name="mode"><?php foreach (MODES as $m): ?><option><?= $m ?></option><?php endforeach; ?></select>
      </label>
      <label>Note<input type="text" name="note" maxlength="255" placeholder="Optional"></label>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn ghost" data-close>Cancel</button>
      <button type="submit" class="btn">Save expense</button>
    </div>
  </form>
</div>

<!-- Add income -->
<div class="modal" id="modal-income" aria-hidden="true">
  <form class="modal-card" action="actions.php" method="post">
    <h3>Add income</h3>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_income">
    <input type="hidden" name="redirect" value="<?= e($self) ?>">
    <div class="form-grid">
      <label>Amount (₹)<input type="number" name="amount" step="0.01" min="0.01" required></label>
      <label>Date<input type="date" name="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></label>
      <label>Source<input type="text" name="source" maxlength="80" placeholder="e.g. Salary, Freelance" required></label>
      <label>Note<input type="text" name="note" maxlength="255" placeholder="Optional"></label>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn ghost" data-close>Cancel</button>
      <button type="submit" class="btn">Save income</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
